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

        //make user info
        $this->makeUserInfo($tempUser, $selected);

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
            // ユーザー情報を取得（メール送信・顧客名用）
            $userInfo = UserInfo::where('temp_user_id', $tempUser->id)->first();
            $customerName = '';
            if ($userInfo) {
                $customerName = trim(($userInfo->last_name ?? '') . ' ' . ($userInfo->first_name ?? ''));
            }

            // GetLinkplusUrl API リクエストパラメータの準備
            // ドキュメントに基づく正しいJSON構造
            $getUrlParam = [
                'ShopID' => $config['shop_id'],
                'ShopPass' => $config['shop_pass'],
                'GuideMailSendFlag' => '1',
                'SendMailAddress' => $tempUser->email,
                'TemplateNo' => 1,
            ];

            // 顧客名がある場合は追加
            if (!empty($customerName)) {
                $getUrlParam['CustomerName'] = $customerName;
            }

            // 本人認証質問（必要に応じて追加可能）
            // $getUrlParam['AuthenticationQuestion1'] = '質問';
            // $getUrlParam['AuthenticationAnswer1'] = '答え';

            // JSON構造に従ったリクエストボディ
            $requestBody = [
                'geturlparam' => $getUrlParam,
                'configid' => 'theta',
                'transaction' => [
                    'OrderID' => $orderId,
                    'Amount' => (string)$amount,
                    'Tax' => '0',
                    'CompleteUrl' => route('guest.payment.complete', ['token' => $tempUser->token]),
                ],
                'credit' => [
                    'JobCd' => 'CAPTURE', // AUTH=仮売上, CAPTURE=即時決済
                ],
            ];

            // デバッグ用ログ（パスワードはマスク）
            $logData = $requestBody;
            $logData['geturlparam']['ShopPass'] = '***'; // パスワードをマスク
            Log::info('GMO GetLinkplusUrl Request', [
                'configid' => 'theta',
                'OrderID' => $orderId,
                'OrderID_length' => strlen($orderId),
                'Amount' => $amount,
                'Tax' => $requestBody['transaction']['Tax'],
                'JobCd' => $requestBody['credit']['JobCd'],
                'URL' => $config['get_linkplus_url'],
                'request_body' => $logData,
            ]);

            /** GetLinkplusUrl API - 決済URL取得（単一ステップ） */
            // JSON形式でリクエストを送信
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout(30)
                ->withOptions([
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => true,
                        CURLOPT_SSL_VERIFYHOST => 2,
                    ],
                ])
                ->post($config['get_linkplus_url'], $requestBody);
                // ->post($config['get_linkplus_url'], $requestParams);

            // 実際に送信されたリクエストの詳細をログに記録
            Log::info('GMO GetLinkplusUrl Response', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'response_headers' => $response->headers(),
            ]);

            // HTTPステータスコードの確認
            if (!$response->successful()) {
                // JSONレスポンスをパース（フォールバック: テキスト形式）
                $errorResult = $response->json();
                if (!$errorResult) {
                    parse_str($response->body(), $errorResult);
                }

                $errCode = $errorResult['ErrCode'] ?? ($errorResult['errCode'] ?? 'UNKNOWN');
                $errInfo = $errorResult['ErrInfo'] ?? ($errorResult['errInfo'] ?? '');

                Log::error('GMO GetLinkplusUrl HTTP Error', [
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                    'ErrCode' => $errCode,
                    'ErrInfo' => $errInfo,
                    'OrderID' => $orderId,
                    'URL' => $config['get_linkplus_url'],
                ]);

                // E91エラーの場合、LinkPlusが有効でない可能性がある
                // EntryTran/ExecTranフローにフォールバック
                if ($errCode === 'E91' || $errCode === 'E91099997') {
                    Log::info('GMO GetLinkplusUrl not available, falling back to EntryTran/ExecTran', [
                        'ErrCode' => $errCode,
                        'OrderID' => $orderId,
                    ]);

                    // EntryTran/ExecTranフローに切り替え
                    return $this->redirectToGmoPaymentEntryExec($tempUser, $selected, $orderId, $amount, $config);
                }

                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済サーバーへの接続に失敗しました。エラーコード: ' . $errCode);
            }

            // JSONレスポンスをパース（フォールバック: テキスト形式）
            $result = $response->json();
            if (!$result) {
                parse_str($response->body(), $result);
            }

            // エラーチェック
            $errCode = $result['ErrCode'] ?? ($result['errCode'] ?? null);
            if (!empty($errCode)) {
                $errInfo = $result['ErrInfo'] ?? ($result['errInfo'] ?? null);

                Log::error('GMO GetLinkplusUrl Error', [
                    'ErrCode' => $errCode,
                    'ErrInfo' => $errInfo,
                    'OrderID' => $orderId,
                    'Amount' => $amount,
                    'ShopID' => $config['shop_id'],
                    'ShopPass_set' => !empty($config['shop_pass']),
                    'Response' => $response->body(),
                ]);

                $errorMessage = '決済処理の開始に失敗しました。';
                if ($errInfo) {
                    $errorMessage .= ' エラー: ' . $errInfo;
                }

                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', $errorMessage);
            }

            // StartURLを取得（JSONまたはテキスト形式に対応）
            $startUrl = $result['LinkUrl'] ?? ($result['LinkUrl'] ?? ($result['LinkUrl'] ?? null));

            if (!$startUrl) {

                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済画面へのリダイレクトに失敗しました。');
            }

            // GMO決済画面へリダイレクト
            return redirect($startUrl);
        } catch (\Exception $e) {

            return redirect()
                ->route('guest.payment.index', ['token' => $tempUser->token])
                ->with('error', '決済処理中にエラーが発生しました。もう一度お試しください。');
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

        // tempUserにuserInfoを追加（ブレードの互換性のため）
        // まずuser_idから取得を試みる
        $userInfo = null;
        if ($user) {
            $userInfo = $user->userInfo;
        }
        // userInfoが見つからない場合、temp_user_idから取得
        if (!$userInfo) {
            $userInfo = UserInfo::where('temp_user_id', $tempUser->id)->first();
        }
        $tempUser->userInfo = $userInfo;

        return view('user.temp_user.payment.complete', compact('tempUser', 'selected', 'user'));
    }

    /**
     * EntryTran/ExecTranフロー（GetLinkplusUrlが利用できない場合のフォールバック）
     */
    private function redirectToGmoPaymentEntryExec($tempUser, $selected, $orderId, $amount, $config)
    {
        try {
            // Step 1: EntryTran - 取引登録
            $entryParams = [
                'ShopID' => $config['shop_id'],
                'ShopPass' => $config['shop_pass'],
                'OrderID' => $orderId,
                'Amount' => (string)$amount,
                'Tax' => '0',
            ];

            Log::info('GMO EntryTran Request (Fallback)', [
                'OrderID' => $orderId,
                'Amount' => $amount,
                'URL' => rtrim($config['api_url'], '/') . '/EntryTran.idPass',
            ]);

            $entryResponse = Http::asForm()
                ->timeout(30)
                ->withOptions([
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => true,
                        CURLOPT_SSL_VERIFYHOST => 2,
                    ],
                ])
                ->post(rtrim($config['api_url'], '/') . '/EntryTran.idPass', $entryParams);

            if (!$entryResponse->successful()) {
                Log::error('GMO EntryTran HTTP Error', [
                    'status_code' => $entryResponse->status(),
                    'body' => $entryResponse->body(),
                ]);
                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済サーバーへの接続に失敗しました。');
            }

            parse_str($entryResponse->body(), $entryResult);

            if (isset($entryResult['ErrCode']) && !empty($entryResult['ErrCode'])) {
                Log::error('GMO EntryTran Error', [
                    'ErrCode' => $entryResult['ErrCode'],
                    'ErrInfo' => $entryResult['ErrInfo'] ?? null,
                ]);
                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済処理の開始に失敗しました。エラー: ' . ($entryResult['ErrInfo'] ?? $entryResult['ErrCode']));
            }

            if (!isset($entryResult['AccessID']) || !isset($entryResult['AccessPass'])) {
                Log::error('GMO EntryTran Missing AccessID/AccessPass');
                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済処理の開始に失敗しました。');
            }

            // Step 2: ExecTran - 決済実行
            $execParams = [
                'SiteID' => $config['site_id'],
                'SitePass' => $config['site_pass'],
                'AccessID' => $entryResult['AccessID'],
                'AccessPass' => $entryResult['AccessPass'],
                'OrderID' => $orderId,
                'Method' => '1', // 一括
                'RetURL' => route('guest.payment.callback', ['token' => $tempUser->token]),
                'CancelURL' => route('guest.payment.cancel', ['token' => $tempUser->token]),
                'ClientField1' => $tempUser->token ?? '',
                'ClientField2' => (string)$selected->id,
            ];

            Log::info('GMO ExecTran Request (Fallback)', [
                'OrderID' => $orderId,
                'AccessID' => $entryResult['AccessID'],
                'URL' => rtrim($config['api_url'], '/') . '/ExecTran.idPass',
            ]);

            $execResponse = Http::asForm()
                ->timeout(30)
                ->withOptions([
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => true,
                        CURLOPT_SSL_VERIFYHOST => 2,
                    ],
                ])
                ->post(rtrim($config['api_url'], '/') . '/ExecTran.idPass', $execParams);

            parse_str($execResponse->body(), $execResult);

            if (isset($execResult['ErrCode']) && !empty($execResult['ErrCode'])) {
                Log::error('GMO ExecTran Error', [
                    'ErrCode' => $execResult['ErrCode'],
                    'ErrInfo' => $execResult['ErrInfo'] ?? null,
                ]);
                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済処理の実行に失敗しました。エラー: ' . ($execResult['ErrInfo'] ?? $execResult['ErrCode']));
            }

            if (!isset($execResult['StartURL'])) {
                Log::error('GMO ExecTran Missing StartURL');
                return redirect()
                    ->route('guest.payment.index', ['token' => $tempUser->token])
                    ->with('error', '決済画面へのリダイレクトに失敗しました。');
            }

            // GMO決済画面へリダイレクト
            return redirect($execResult['StartURL']);
        } catch (\Exception $e) {
            Log::error('GMO EntryTran/ExecTran Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()
                ->route('guest.payment.index', ['token' => $tempUser->token])
                ->with('error', '決済処理中にエラーが発生しました。もう一度お試しください。');
        }
    }

    /**
     * make user info
     */
    private function makeUserInfo($tempUser, $selected)
    {
        $userInfo = UserInfo::where('temp_user_id', $tempUser->id)->first();

        // UserInfoが存在しない場合はエラー
        if (!$userInfo) {
            Log::error('UserInfo not found for temp_user', [
                'temp_user_id' => $tempUser->id,
                'temp_user_email' => $tempUser->email,
            ]);
            throw new \Exception('ユーザー情報が見つかりませんでした。');
        }

        // 既にユーザーが作成されているかチェック
        $user = null;
        if ($userInfo->user_id) {
            $user = User::find($userInfo->user_id);
        }

        // user_idが設定されていてもユーザーが存在しない場合、または
        // 同じメールアドレスのユーザーが既に存在する場合をチェック
        if (!$user) {
            // メールアドレスで既存ユーザーを検索
            $existingUser = User::where('email', $tempUser->email)->first();

            if ($existingUser) {
                // 既存ユーザーが見つかった場合、それを使用
                $user = $existingUser;

                // UserInfoを既存ユーザーに紐付け（まだ紐付けられていない場合）
                if (!$userInfo->user_id) {
                    $userInfo->user_id = $user->id;
                    $userInfo->save();
                }
            } else {
                // 新規ユーザーを作成
                try {
                    DB::beginTransaction();

                    // 一時パスワードを生成（12文字、英数字記号）
                    $temporaryPassword = Str::random(12);

                    // ユーザー名を生成（姓 + 名）
                    $lastName = $userInfo->last_name ?? '';
                    $firstName = $userInfo->first_name ?? '';
                    $userName = trim($lastName . ' ' . $firstName);

                    // ユーザー名が空の場合はメールアドレスを使用
                    if (empty($userName)) {
                        $userName = $tempUser->email;
                    }

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

                    // SelectedItemをユーザーに紐付け（まだ紐付けられていない場合）
                    if (!$selected->user_id) {
                        $selected->user_id = $user->id;
                        $selected->save();
                    }

                    DB::commit();

                    // パスワード通知メールを送信
                    SendMessageJob::dispatch(
                        $user->email,
                        '会員登録が完了しました',
                        'mails.user.auth.password_notification',
                        [
                            'email' => $user->email,
                            'name' => $userName,
                            'phone' => $userInfo->phone_number,
                            'password' => $temporaryPassword,
                        ]
                    )->afterCommit();

                    Log::info('User created after payment success', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'temp_user_id' => $tempUser->id,
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error creating user in makeUserInfo', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'temp_user_id' => $tempUser->id,
                        'email' => $tempUser->email,
                    ]);
                    throw $e;
                }
            }
        }

        // 既存ユーザーの場合、SelectedItemのみ紐付け（まだ紐付けられていない場合）
        if ($user && !$selected->user_id) {
            $selected->user_id = $user->id;
            $selected->save();
        }
    }
}
