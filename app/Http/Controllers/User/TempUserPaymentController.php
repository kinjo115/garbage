<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TempUser;
use App\Models\SelectedItem;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendMessageJob;
use Carbon\Carbon;

class TempUserPaymentController extends Controller
{
    public function index($token)
    {
        $tempUser = TempUser::where('token', $token)->firstOrFail();

        $selected = SelectedItem::where('temp_user_id', $tempUser->id)
            ->whereNull('user_id')
            ->first();

        if (!$selected) {
            return redirect()
                ->route('guest.item.index', ['token' => $token])
                ->with('error', '品目が選択されていません。');
        }

        if ($selected->confirm_status !== SelectedItem::CONFIRM_STATUS_CONFIRMED) {
            return redirect()
                ->route('guest.confirmation.index', ['token' => $token])
                ->with('error', '申込内容が確認されていません。申込内容の確認ページに戻ってください。');
        }

        return view('user.temp_user.payment.index', compact('tempUser', 'selected'));
    }

    /**
     * オンライン決済（GMOペイメント）の開始
     */
    public function store(Request $request, $token)
    {
        $tempUser = TempUser::where('token', $token)->firstOrFail();

        $selected = SelectedItem::where('temp_user_id', $tempUser->id)
            ->whereNull('user_id')
            ->first();

        if (!$selected || $selected->confirm_status !== SelectedItem::CONFIRM_STATUS_CONFIRMED) {
            return redirect()
                ->route('guest.confirmation.index', ['token' => $token])
                ->with('error', '申込内容が確認されていません。');
        }

        $paymentMethod = $request->input('payment_method');

        if ($paymentMethod === 'online') {
            // GMOペイメントへのリダイレクト
            return $this->redirectToGmoPayment($tempUser, $selected);
        } elseif ($paymentMethod === 'convenience') {
            // コンビニ決済の処理（後で実装）
            return redirect()
                ->route('guest.payment.convenience', ['token' => $token]);
        }

        return back()->withErrors('支払い方法を選択してください。');
    }

    /**
     * GMOペイメントへのリダイレクト処理
     */
    private function redirectToGmoPayment($tempUser, $selected)
    {
        $config = config('services.gmo_payment');

        // 設定値の検証
        if (empty($config['shop_id']) || empty($config['shop_pass'])) {
            Log::error('GMO Payment: Missing ShopID or ShopPass configuration', [
                'shop_id_set' => !empty($config['shop_id']),
                'shop_pass_set' => !empty($config['shop_pass']),
            ]);
            return redirect()
                ->route('guest.payment.index', ['token' => $tempUser->token])
                ->with('error', '決済設定が正しくありません。管理者にお問い合わせください。');
        }

        // ShopID/ShopPass の形式チェック
        if (strlen($config['shop_id']) < 10 || strlen($config['shop_pass']) < 8) {
            Log::error('GMO Payment: ShopID or ShopPass format seems invalid', [
                'shop_id_length' => strlen($config['shop_id']),
                'shop_pass_length' => strlen($config['shop_pass']),
            ]);
        }

        // オーダーIDを生成（一意のID、27文字以内、英数字のみ）
        // GMOペイメントのOrderIDは英数字のみ（ハイフン不可）
        $orderId = 'ORD' . str_pad($tempUser->id, 6, '0', STR_PAD_LEFT) . time() . Str::random(6);
        $orderId = substr($orderId, 0, 27); // GMOペイメントのOrderIDは27文字以内
        // 英数字のみに変換（念のため）
        $orderId = preg_replace('/[^A-Za-z0-9]/', '', $orderId);

        // 決済金額（税込）- 整数に変換（GMO APIは文字列として送信）
        $amount = (int)$selected->total_amount;

        if ($amount <= 0) {
            Log::error('GMO Payment: Invalid amount', [
                'amount' => $selected->total_amount,
                'selected_item_id' => $selected->id,
            ]);
            return redirect()
                ->route('guest.payment.index', ['token' => $tempUser->token])
                ->with('error', '決済金額が無効です。');
        }

        // 決済情報を保存
        $selected->payment_method = 'online';
        $selected->transaction_id = $orderId;
        $selected->payment_status = 1; // pending
        $selected->save();

        try {
            // EntryTran リクエストパラメータの準備
            // GMO APIは数値パラメータも文字列として送信する必要がある場合がある
            $entryParams = [
                'ShopID' => $config['shop_id'],
                'ShopPass' => $config['shop_pass'],
                'OrderID' => $orderId,
                'Amount' => (string)$amount, // 文字列として送信
                'Tax' => '0', // 税額（今回は0、文字列として送信）
            ];

            // デバッグ用ログ（パスワードはマスク）
            Log::info('GMO EntryTran Request', [
                'ShopID' => $config['shop_id'],
                'ShopPass' => !empty($config['shop_pass']) ? '***' : 'EMPTY',
                'ShopPass_length' => strlen($config['shop_pass'] ?? ''),
                'OrderID' => $orderId,
                'OrderID_length' => strlen($orderId),
                'Amount' => $amount,
                'Amount_type' => gettype($entryParams['Amount']),
                'Tax' => $entryParams['Tax'],
                'Tax_type' => gettype($entryParams['Tax']),
                'URL' => $config['entry_url'],
                'AllParams' => array_map(function ($key, $value) {
                    return $key === 'ShopPass' ? '***' : $value;
                }, array_keys($entryParams), $entryParams),
            ]);

            /** Step1: EntryTran - 取引登録 */
            $entryResponse = Http::asForm()
                ->timeout(30)
                ->post($config['entry_url'], $entryParams);

            // 実際に送信されたリクエストの詳細をログに記録
            Log::info('GMO EntryTran Response', [
                'status_code' => $entryResponse->status(),
                'response_body' => $entryResponse->body(),
                'response_headers' => $entryResponse->headers(),
            ]);

            // HTTPステータスコードの確認
            if (!$entryResponse->successful()) {
                Log::error('GMO EntryTran HTTP Error', [
                    'status_code' => $entryResponse->status(),
                    'body' => $entryResponse->body(),
                    'OrderID' => $orderId,
                ]);

                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済サーバーへの接続に失敗しました。');
            }

            parse_str($entryResponse->body(), $entryResult);

            // エラーチェック
            if (isset($entryResult['ErrCode']) && !empty($entryResult['ErrCode'])) {
                Log::error('GMO EntryTran Error', [
                    'ErrCode' => $entryResult['ErrCode'] ?? null,
                    'ErrInfo' => $entryResult['ErrInfo'] ?? null,
                    'OrderID' => $orderId,
                    'Amount' => $amount,
                    'ShopID' => $config['shop_id'],
                    'ShopPass_set' => !empty($config['shop_pass']),
                    'Response' => $entryResponse->body(),
                    'RequestParams' => [
                        'ShopID' => $config['shop_id'],
                        'OrderID' => $orderId,
                        'Amount' => $amount,
                        'Tax' => 0,
                    ],
                ]);

                $errorMessage = '決済処理の開始に失敗しました。';
                if (isset($entryResult['ErrInfo'])) {
                    $errorMessage .= ' エラー: ' . $entryResult['ErrInfo'];
                }

                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', $errorMessage);
            }

            if (!isset($entryResult['AccessID']) || !isset($entryResult['AccessPass'])) {
                Log::error('GMO EntryTran Missing AccessID/AccessPass', [
                    'Response' => $entryResponse->body(),
                    'Parsed' => $entryResult,
                ]);

                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済処理の開始に失敗しました。');
            }

            /** Step2: ExecTran - 決済実行（リンクタイプPlus） */
            $execResponse = Http::asForm()->post($config['exec_url'], [
                'SiteID' => $config['site_id'],
                'SitePass' => $config['site_pass'],
                'AccessID' => $entryResult['AccessID'],
                'AccessPass' => $entryResult['AccessPass'],
                'OrderID' => $orderId,
                'Method' => '1', // 一括
                'RetURL' => route('guest.payment.callback', ['token' => $tempUser->token]),
                'CancelURL' => route('guest.payment.cancel', ['token' => $tempUser->token]),
                'ClientField1' => $tempUser->token ?? '', // トークンを保持
                'ClientField2' => (string)$selected->id, // SelectedItem IDを保持
                'ClientField3' => '', // 追加フィールド
            ]);

            parse_str($execResponse->body(), $execResult);

            // エラーチェック
            if (isset($execResult['ErrCode']) && !empty($execResult['ErrCode'])) {
                Log::error('GMO ExecTran Error', [
                    'ErrCode' => $execResult['ErrCode'] ?? null,
                    'ErrInfo' => $execResult['ErrInfo'] ?? null,
                    'OrderID' => $orderId,
                    'AccessID' => $entryResult['AccessID'],
                    'Response' => $execResponse->body(),
                ]);

                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済処理の実行に失敗しました。エラーコード: ' . ($execResult['ErrCode'] ?? 'UNKNOWN'));
            }

            if (!isset($execResult['StartURL'])) {
                Log::error('GMO ExecTran Missing StartURL', [
                    'Response' => $execResponse->body(),
                    'Parsed' => $execResult,
                ]);

                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済画面へのリダイレクトに失敗しました。');
            }

            // GMO決済画面へリダイレクト
            return redirect($execResult['StartURL']);
        } catch (\Exception $e) {
            Log::error('GMO Payment Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'OrderID' => $orderId,
            ]);

            return redirect()
                ->route('guest.payment.index', ['token' => $tempUser->token])
                ->with('error', '決済処理中にエラーが発生しました。もう一度お試しください。');
        }
    }

    /**
     * GMOペイメントからのコールバック（決済完了）
     */
    public function callback(Request $request, $token)
    {
        $tempUser = TempUser::where('token', $token)->firstOrFail();

        $selected = SelectedItem::where('temp_user_id', $tempUser->id)
            ->whereNull('user_id')
            ->first();

        if (!$selected) {
            Log::error('GMO Callback: SelectedItem not found', [
                'token' => $token,
                'temp_user_id' => $tempUser->id,
                'request_params' => $request->all(),
            ]);

            return redirect()
                ->route('home')
                ->with('error', '決済情報が見つかりませんでした。');
        }

        // GMOペイメントからのレスポンスを確認
        $orderId = $request->input('OrderID');
        $status = $request->input('Status');
        $accessId = $request->input('AccessID');
        $errCode = $request->input('ErrCode');
        $errInfo = $request->input('ErrInfo');

        // エラーログ記録
        if (!empty($errCode)) {
            Log::error('GMO Callback Error', [
                'ErrCode' => $errCode,
                'ErrInfo' => $errInfo,
                'OrderID' => $orderId,
                'AccessID' => $accessId,
                'Status' => $status,
                'all_params' => $request->all(),
            ]);

            $selected->payment_status = 0; // not paid
            $selected->save();

            return redirect()
                ->route('guest.payment.index', ['token' => $token])
                ->with('error', '決済に失敗しました。エラーコード: ' . $errCode . '。もう一度お試しください。');
        }

        // 決済成功の判定
        // GMOペイメントのStatus: CAPTURE(即時決済完了), AUTH(仮売上)
        if ($status === 'CAPTURE' || $status === 'AUTH') {
            try {
                DB::beginTransaction();

                // 決済成功
                $selected->payment_status = 2; // paid
                $selected->payment_date = now();
                $selected->save();

                // ユーザー情報を取得
                $userInfo = UserInfo::where('temp_user_id', $tempUser->id)->first();

                if (!$userInfo) {
                    throw new \Exception('ユーザー情報が見つかりませんでした。');
                }

                // 既にユーザーが作成されているかチェック
                $user = null;
                if ($userInfo->user_id) {
                    $user = User::find($userInfo->user_id);
                }

                // ユーザーが存在しない場合は新規作成
                if (!$user) {
                    // 一時パスワードを生成（12文字、英数字記号）
                    $temporaryPassword = Str::random(12);

                    // ユーザー名を生成（姓 + 名）
                    $userName = trim($userInfo->last_name . ' ' . $userInfo->first_name);

                    // ユーザーを作成
                    $user = User::create([
                        'name' => $userName,
                        'email' => $tempUser->email,
                        'password' => Hash::make($temporaryPassword),
                        'role' => User::ROLE['USER'],
                    ]);

                    // UserInfoをユーザーに紐付け
                    $userInfo->user_id = $user->id;
                    $userInfo->save();

                    // SelectedItemをユーザーに紐付け
                    $selected->user_id = $user->id;
                    $selected->save();

                    // パスワード通知メールを送信
                    SendMessageJob::dispatch(
                        $user->email,
                        '会員登録が完了しました',
                        'mails.user.auth.password_notification',
                        [
                            'email' => $user->email,
                            'name' => $userName,
                            'password' => $temporaryPassword,
                        ]
                    )->afterCommit();

                    Log::info('User created after payment success', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'temp_user_id' => $tempUser->id,
                    ]);
                } else {
                    // 既存ユーザーの場合、SelectedItemのみ紐付け
                    $selected->user_id = $user->id;
                    $selected->save();
                }

                DB::commit();

                // 決済完了メールを送信
                $userName = $user ? $user->name : trim(($userInfo->last_name ?? '') . ' ' . ($userInfo->first_name ?? ''));
                $email = $user ? $user->email : $tempUser->email;

                // 収集日のフォーマット
                $collectionDate = $selected->collection_date
                    ? Carbon::parse($selected->collection_date)->format('Y年n月j日')
                    : null;

                // 支払い方法の表示名
                $paymentMethodName = 'オンライン決済';
                if ($selected->payment_method === 'convenience') {
                    $paymentMethodName = 'コンビニ決済';
                }

                SendMessageJob::dispatch(
                    $email,
                    '決済が完了しました',
                    'mails.user.payment.success',
                    [
                        'name' => $userName,
                        'email' => $email,
                        'order_id' => $orderId,
                        'payment_date' => Carbon::now()->format('Y年n月j日 H:i'),
                        'payment_amount' => $selected->total_amount,
                        'payment_method' => $paymentMethodName,
                        'collection_date' => $collectionDate,
                        'selected_items' => $selected->selected_items ?? [],
                        'total_amount' => $selected->total_amount,
                        'total_quantity' => $selected->total_quantity,
                    ]
                )->afterCommit();

                Log::info('GMO Payment Success', [
                    'OrderID' => $orderId,
                    'Status' => $status,
                    'AccessID' => $accessId,
                    'selected_item_id' => $selected->id,
                    'user_id' => $user->id ?? null,
                    'email_sent' => true,
                ]);

                // 受付完了処理
                return redirect()
                    ->route('guest.payment.complete', ['token' => $token])
                    ->with('success', '決済が完了しました。');
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Error creating user after payment', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'temp_user_id' => $tempUser->id,
                ]);

                // 決済は成功しているが、ユーザー作成に失敗した場合
                $selected->payment_status = 2; // paid
                $selected->payment_date = now();
                $selected->save();

                return redirect()
                    ->route('guest.payment.complete', ['token' => $token])
                    ->with('warning', '決済は完了しましたが、会員登録処理中にエラーが発生しました。管理者にお問い合わせください。');
            }
        } else {
            // 決済失敗または不明なステータス
            Log::warning('GMO Callback Unknown Status', [
                'Status' => $status,
                'OrderID' => $orderId,
                'AccessID' => $accessId,
                'all_params' => $request->all(),
            ]);

            $selected->payment_status = 0; // not paid
            $selected->save();

            return redirect()
                ->route('guest.payment.index', ['token' => $token])
                ->with('error', '決済に失敗しました。もう一度お試しください。');
        }
    }

    /**
     * GMOペイメントからのキャンセル
     */
    public function cancel($token)
    {
        return redirect()
            ->route('guest.payment.index', ['token' => $token])
            ->with('info', '決済がキャンセルされました。');
    }

    /**
     * 決済完了ページ
     */
    public function complete($token)
    {
        $tempUser = TempUser::where('token', $token)->firstOrFail();

        $selected = SelectedItem::where('temp_user_id', $tempUser->id)
            ->whereNotNull('user_id')
            ->first();

        if (!$selected || $selected->payment_status !== 2) {
            return redirect()
                ->route('guest.payment.index', ['token' => $token])
                ->with('error', '決済情報が見つかりませんでした。');
        }

        $user = User::find($selected->user_id);

        return view('user.temp_user.payment.complete', compact('tempUser', 'selected', 'user'));
    }
}
