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
            // GetLinkplusUrl API リクエストパラメータの準備
            $requestParams = [
                'ShopID' => $config['shop_id'],
                'ShopPass' => $config['shop_pass'],
                'ConfigID' => $config['config_id'] ?? '001',
                'OrderID' => $orderId,
                'Amount' => $amount,
                'Tax' => 0,
                'RetURL' => route('guest.payment.callback', ['token' => $tempUser->token]),
                'JobCd' => 'CAPTURE', // 即時決済
            ];

            // デバッグ用ログ（パスワードはマスク）
            Log::info('GMO GetLinkplusUrl Request', [
                'ShopID' => $config['shop_id'],
                'ShopPass' => !empty($config['shop_pass']) ? '***' : 'EMPTY',
                'ShopPass_length' => strlen($config['shop_pass'] ?? ''),
                'ConfigID' => $requestParams['ConfigID'],
                'OrderID' => $orderId,
                'OrderID_length' => strlen($orderId),
                'Amount' => $amount,
                'Tax' => $requestParams['Tax'],
                'RetURL' => $requestParams['RetURL'],
                'URL' => $config['get_linkplus_url'],
            ]);

            /** GetLinkplusUrl API - 決済URL取得（単一ステップ） */
            $response = Http::asForm()
                ->timeout(30)
                ->withOptions([
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => true,
                        CURLOPT_SSL_VERIFYHOST => 2,
                    ],
                ])
                ->post($config['get_linkplus_url'], $requestParams);

            // 実際に送信されたリクエストの詳細をログに記録
            Log::info('GMO GetLinkplusUrl Response', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'response_headers' => $response->headers(),
            ]);

            // HTTPステータスコードの確認
            if (!$response->successful()) {
                Log::error('GMO GetLinkplusUrl HTTP Error', [
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                    'OrderID' => $orderId,
                ]);

                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済サーバーへの接続に失敗しました。');
            }

            parse_str($response->body(), $result);

            // エラーチェック
            if (isset($result['ErrCode']) && !empty($result['ErrCode'])) {
                Log::error('GMO GetLinkplusUrl Error', [
                    'ErrCode' => $result['ErrCode'] ?? null,
                    'ErrInfo' => $result['ErrInfo'] ?? null,
                    'OrderID' => $orderId,
                    'Amount' => $amount,
                    'ShopID' => $config['shop_id'],
                    'ShopPass_set' => !empty($config['shop_pass']),
                    'Response' => $response->body(),
                ]);

                $errorMessage = '決済処理の開始に失敗しました。';
                if (isset($result['ErrInfo'])) {
                    $errorMessage .= ' エラー: ' . $result['ErrInfo'];
                }

                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', $errorMessage);
            }

            if (!isset($result['StartURL'])) {
                Log::error('GMO GetLinkplusUrl Missing StartURL', [
                    'Response' => $response->body(),
                    'Parsed' => $result,
                ]);

                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済画面へのリダイレクトに失敗しました。');
            }

            // GMO決済画面へリダイレクト
            return redirect($result['StartURL']);
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

        // GMOペイメントからのレスポンスを確認
        $orderId = $request->input('OrderID');
        $status = $request->input('Status');
        $accessId = $request->input('AccessID');
        $errCode = $request->input('ErrCode');
        $errInfo = $request->input('ErrInfo');

        // SelectedItemを検索（transaction_idで検索するか、temp_user_idで検索）
        $selected = null;
        if ($orderId) {
            // まずtransaction_idで検索（より確実）
            $selected = SelectedItem::where('transaction_id', $orderId)
                ->where('temp_user_id', $tempUser->id)
                ->first();
        }

        // transaction_idで見つからない場合、temp_user_idで検索
        if (!$selected) {
            $selected = SelectedItem::where('temp_user_id', $tempUser->id)
                ->whereNull('user_id')
                ->first();
        }

        // それでも見つからない場合、user_idが設定されている場合も含めて検索
        if (!$selected) {
            $selected = SelectedItem::where('temp_user_id', $tempUser->id)
                ->where('transaction_id', $orderId)
                ->first();
        }

        if (!$selected) {
            Log::error('GMO Callback: SelectedItem not found', [
                'token' => $token,
                'temp_user_id' => $tempUser->id,
                'order_id' => $orderId,
                'request_params' => $request->all(),
            ]);

            return redirect()
                ->route('home')
                ->with('error', '決済情報が見つかりませんでした。');
        }

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

        // 既に決済が完了している場合は、完了ページにリダイレクト（重複処理を防ぐ）
        if ($selected->payment_status === 2 && $selected->payment_date) {
            Log::info('GMO Callback: Payment already processed', [
                'OrderID' => $orderId,
                'selected_item_id' => $selected->id,
            ]);

            return redirect()
                ->route('guest.payment.complete', ['token' => $token])
                ->with('info', '決済は既に完了しています。');
        }

        // 決済成功の判定
        // GMOペイメントのStatus: CAPTURE(即時決済完了), AUTH(仮売上)
        if ($status === 'CAPTURE' || $status === 'AUTH') {
            try {
                DB::beginTransaction();

                // 決済成功
                $selected->payment_status = 2; // paid
                $selected->payment_date = now();

                // 受付番号のシリアル番号を生成（申請IDの下5桁）
                if (!$selected->reception_number_serial) {
                    $selectedId = (string)$selected->id;
                    $last5Digits = substr($selectedId, -5); // 下5桁を取得
                    $selected->reception_number_serial = str_pad($last5Digits, 5, '0', STR_PAD_LEFT); // 5桁にゼロパディング
                }

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

                // 受付番号を生成（YYMM-00001形式）
                $paymentDate = Carbon::now();
                $yy = $paymentDate->format('y'); // 2桁の年
                $mm = $paymentDate->format('m'); // 2桁の月
                $serial = $selected->reception_number_serial ?? '00001';
                $receptionNumber = $yy . $mm . '-' . $serial;

                SendMessageJob::dispatch(
                    $email,
                    '決済が完了しました',
                    'mails.user.payment.success',
                    [
                        'name' => $userName,
                        'email' => $email,
                        'order_id' => $orderId,
                        'reception_number' => $receptionNumber,
                        'payment_date' => $paymentDate->format('Y年n月j日 H:i'),
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
     * コンビニ決済ページ
     */
    public function convenience($token)
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

        // コンビニ決済の処理（後で実装）
        // 現在は一時的に支払い方法を保存して完了ページにリダイレクト
        $selected->payment_method = 'convenience';
        $selected->payment_status = 1; // pending
        $selected->save();

        return view('user.temp_user.payment.convenience', compact('tempUser', 'selected'));
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
