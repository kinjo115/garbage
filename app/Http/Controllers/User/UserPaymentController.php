<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SelectedItem;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendMessageJob;
use Carbon\Carbon;

class UserPaymentController extends Controller
{
    public function index($id)
    {
        $user = Auth::user();

        $selected = SelectedItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($selected->confirm_status !== SelectedItem::CONFIRM_STATUS_CONFIRMED) {
            return redirect()
                ->route('user.items.confirmation', ['id' => $id])
                ->with('error', '申込内容が確認されていません。申込内容の確認ページに戻ってください。');
        }

        // 既に決済済みの場合は詳細ページにリダイレクト
        if ($selected->payment_status === 2) {
            return redirect()
                ->route('user.items.show', ['id' => $id])
                ->with('info', 'この申込みは既に決済済みです。');
        }

        // TempUser形式で渡す（ブレードの互換性のため）
        $tempUser = (object)[
            'token' => null, // 認証済みユーザーなのでトークンは不要
            'email' => $user->email,
        ];

        return view('user.temp_user.payment.index', compact('tempUser', 'selected'));
    }

    /**
     * オンライン決済（GMOペイメント）の開始
     */
    public function store(Request $request, $id)
    {
        $user = Auth::user();

        $selected = SelectedItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($selected->confirm_status !== SelectedItem::CONFIRM_STATUS_CONFIRMED) {
            return redirect()
                ->route('user.items.confirmation', ['id' => $id])
                ->with('error', '申込内容が確認されていません。');
        }

        $paymentMethod = $request->input('payment_method');

        if ($paymentMethod === 'online') {
            // GMOペイメントへのリダイレクト
            return $this->redirectToGmoPayment($user, $selected);
        } elseif ($paymentMethod === 'convenience') {
            // コンビニ決済の処理
            return redirect()
                ->route('user.payment.convenience', ['id' => $id]);
        }

        return back()->withErrors('支払い方法を選択してください。');
    }

    /**
     * GMOペイメントへのリダイレクト処理
     */
    private function redirectToGmoPayment($user, $selected)
    {
        $config = config('services.gmo_payment');

        // 設定値の検証
        if (empty($config['shop_id']) || empty($config['shop_pass'])) {
            Log::error('GMO Payment: Missing ShopID or ShopPass configuration', [
                'shop_id_set' => !empty($config['shop_id']),
                'shop_pass_set' => !empty($config['shop_pass']),
            ]);
            return redirect()
                ->route('user.payment.index', ['id' => $selected->id])
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
        $orderId = 'ORD' . str_pad($selected->id, 6, '0', STR_PAD_LEFT) . time() . Str::random(6);
        $orderId = substr($orderId, 0, 27);
        $orderId = preg_replace('/[^A-Za-z0-9]/', '', $orderId);

        // 決済金額（税込）- 整数に変換
        $amount = (int)$selected->total_amount;

        if ($amount <= 0) {
            Log::error('GMO Payment: Invalid amount', [
                'amount' => $selected->total_amount,
                'selected_item_id' => $selected->id,
            ]);
            return redirect()
                ->route('user.payment.index', ['id' => $selected->id])
                ->with('error', '決済金額が無効です。');
        }

        // 決済情報を保存
        $selected->payment_method = 'online';
        $selected->transaction_id = $orderId;
        $selected->payment_status = 1; // pending
        $selected->save();

        try {
            // ユーザー情報を取得（メール送信・顧客名用）
            $userInfo = $user->userInfo;
            $customerName = '';
            if ($userInfo) {
                $customerName = trim(($userInfo->last_name ?? '') . ' ' . ($userInfo->first_name ?? ''));
            }

            // GetLinkplusUrl API リクエストパラメータの準備
            $getUrlParam = [
                'ShopID' => $config['shop_id'],
                'ShopPass' => $config['shop_pass'],
                'GuideMailSendFlag' => '1',
                'SendMailAddress' => $user->email,
                'TemplateNo' => 1,
            ];

            // 顧客名がある場合は追加
            if (!empty($customerName)) {
                $getUrlParam['CustomerName'] = $customerName;
            }

            // TemplateNo（テンプレート番号）を設定

            // JSON構造に従ったリクエストボディ
            $requestBody = [
                'geturlparam' => $getUrlParam,
                'configid' => $config['config_id'] ?? '001',
                'transaction' => [
                    'OrderID' => $orderId,
                    'Amount' => (string)$amount,
                    'Tax' => '0',
                    'CompleteUrl' => route('user.payment.complete', ['id' => $selected->id]),
                ],
                'credit' => [
                    'JobCd' => 'CAPTURE', // AUTH=仮売上, CAPTURE=即時決済
                ],
            ];

            // デバッグ用ログ（パスワードはマスク）
            $logData = $requestBody;
            $logData['geturlparam']['ShopPass'] = '***';
            Log::info('GMO GetLinkplusUrl Request (Authenticated User)', [
                'configid' => $requestBody['configid'],
                'OrderID' => $orderId,
                'OrderID_length' => strlen($orderId),
                'Amount' => $amount,
                'user_id' => $user->id,
                'selected_item_id' => $selected->id,
                'request_body' => $logData,
            ]);

            /** GetLinkplusUrl API - 決済URL取得（単一ステップ） */
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

            Log::info('GMO GetLinkplusUrl Response (Authenticated User)', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

            // HTTPステータスコードの確認
            if (!$response->successful()) {
                $errorResult = $response->json();
                if (!$errorResult) {
                    parse_str($response->body(), $errorResult);
                }

                $errCode = $errorResult['ErrCode'] ?? ($errorResult['errCode'] ?? 'UNKNOWN');
                $errInfo = $errorResult['ErrInfo'] ?? ($errorResult['errInfo'] ?? '');

                Log::error('GMO GetLinkplusUrl HTTP Error (Authenticated User)', [
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                    'ErrCode' => $errCode,
                    'ErrInfo' => $errInfo,
                    'OrderID' => $orderId,
                ]);

                // E91エラーの場合、EntryTran/ExecTranフローにフォールバック
                if ($errCode === 'E91' || $errCode === 'E91099997') {
                    Log::info('GMO GetLinkplusUrl not available, falling back to EntryTran/ExecTran', [
                        'ErrCode' => $errCode,
                        'OrderID' => $orderId,
                    ]);

                    return $this->redirectToGmoPaymentEntryExec($user, $selected, $orderId, $amount, $config);
                }

                return redirect()
                    ->route('user.payment.index', ['id' => $selected->id])
                    ->with('error', '決済サーバーへの接続に失敗しました。エラーコード: ' . $errCode);
            }

            // JSONレスポンスをパース
            $result = $response->json();
            if (!$result) {
                parse_str($response->body(), $result);
            }

            // エラーチェック
            $errCode = $result['ErrCode'] ?? ($result['errCode'] ?? null);
            if (!empty($errCode)) {
                $errInfo = $result['ErrInfo'] ?? ($result['errInfo'] ?? null);

                Log::error('GMO GetLinkplusUrl Error (Authenticated User)', [
                    'ErrCode' => $errCode,
                    'ErrInfo' => $errInfo,
                    'OrderID' => $orderId,
                    'Amount' => $amount,
                ]);

                $errorMessage = '決済処理の開始に失敗しました。';
                if ($errInfo) {
                    $errorMessage .= ' エラー: ' . $errInfo;
                }

                return redirect()
                    ->route('user.payment.index', ['id' => $selected->id])
                    ->with('error', $errorMessage);
            }

            // StartURLを取得
            $startUrl = $result['LinkUrl'] ?? ($result['LinkUrl'] ?? ($result['LinkUrl'] ?? null));

            if (!$startUrl) {
                Log::error('GMO GetLinkplusUrl Missing StartURL (Authenticated User)', [
                    'Response' => $response->body(),
                    'Parsed' => $result,
                ]);

                return redirect()
                    ->route('user.payment.index', ['id' => $selected->id])
                    ->with('error', '決済画面へのリダイレクトに失敗しました。');
            }

            // GMO決済画面へリダイレクト
            return redirect($startUrl);
        } catch (\Exception $e) {
            Log::error('GMO Payment Exception (Authenticated User)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'OrderID' => $orderId,
                'user_id' => $user->id,
            ]);

            return redirect()
                ->route('user.payment.index', ['id' => $selected->id])
                ->with('error', '決済処理中にエラーが発生しました。もう一度お試しください。');
        }
    }

    /**
     * コンビニ決済ページ
     */
    public function convenience($id)
    {
        $user = Auth::user();

        $selected = SelectedItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($selected->confirm_status !== SelectedItem::CONFIRM_STATUS_CONFIRMED) {
            return redirect()
                ->route('user.items.confirmation', ['id' => $id])
                ->with('error', '申込内容が確認されていません。申込内容の確認ページに戻ってください。');
        }

        // コンビニ決済の処理
        $selected->payment_method = 'convenience';
        $selected->payment_status = 1; // pending
        $selected->save();

        // TempUser形式で渡す（ブレードの互換性のため）
        $tempUser = (object)[
            'token' => null,
            'email' => $user->email,
        ];

        return view('user.temp_user.payment.convenience', compact('tempUser', 'selected'));
    }

    /**
     * GMOペイメントからのキャンセル
     */
    public function cancel($id)
    {
        return redirect()
            ->route('user.payment.index', ['id' => $id])
            ->with('info', '決済がキャンセルされました。');
    }

    /**
     * 決済完了ページ
     */
    public function complete($id)
    {
        $user = Auth::user();

        $selected = SelectedItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();


        // TempUser形式で渡す（ブレードの互換性のため）
        $tempUser = (object)[
            'token' => null,
            'email' => $user->email,
        ];

        return view('user.temp_user.payment.complete', compact('tempUser', 'selected', 'user'));
    }

    /**
     * EntryTran/ExecTranフロー（GetLinkplusUrlが利用できない場合のフォールバック）
     */
    private function redirectToGmoPaymentEntryExec($user, $selected, $orderId, $amount, $config)
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

            Log::info('GMO EntryTran Request (Fallback - Authenticated User)', [
                'OrderID' => $orderId,
                'Amount' => $amount,
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
                Log::error('GMO EntryTran HTTP Error (Authenticated User)', [
                    'status_code' => $entryResponse->status(),
                    'body' => $entryResponse->body(),
                ]);
                return redirect()
                    ->route('user.payment.index', ['id' => $selected->id])
                    ->with('error', '決済サーバーへの接続に失敗しました。');
            }

            parse_str($entryResponse->body(), $entryResult);

            if (isset($entryResult['ErrCode']) && !empty($entryResult['ErrCode'])) {
                Log::error('GMO EntryTran Error (Authenticated User)', [
                    'ErrCode' => $entryResult['ErrCode'],
                    'ErrInfo' => $entryResult['ErrInfo'] ?? null,
                ]);
                return redirect()
                    ->route('user.payment.index', ['id' => $selected->id])
                    ->with('error', '決済処理の開始に失敗しました。エラー: ' . ($entryResult['ErrInfo'] ?? $entryResult['ErrCode']));
            }

            if (!isset($entryResult['AccessID']) || !isset($entryResult['AccessPass'])) {
                Log::error('GMO EntryTran Missing AccessID/AccessPass (Authenticated User)');
                return redirect()
                    ->route('user.payment.index', ['id' => $selected->id])
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
                'RetURL' => route('user.payment.callback', ['id' => $selected->id]),
                'CancelURL' => route('user.payment.cancel', ['id' => $selected->id]),
                'ClientField1' => (string)$user->id,
                'ClientField2' => (string)$selected->id,
            ];

            Log::info('GMO ExecTran Request (Fallback - Authenticated User)', [
                'OrderID' => $orderId,
                'AccessID' => $entryResult['AccessID'],
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
                Log::error('GMO ExecTran Error (Authenticated User)', [
                    'ErrCode' => $execResult['ErrCode'],
                    'ErrInfo' => $execResult['ErrInfo'] ?? null,
                ]);
                return redirect()
                    ->route('user.payment.index', ['id' => $selected->id])
                    ->with('error', '決済処理の実行に失敗しました。エラー: ' . ($execResult['ErrInfo'] ?? $execResult['ErrCode']));
            }

            if (!isset($execResult['StartURL'])) {
                Log::error('GMO ExecTran Missing StartURL (Authenticated User)');
                return redirect()
                    ->route('user.payment.index', ['id' => $selected->id])
                    ->with('error', '決済画面へのリダイレクトに失敗しました。');
            }

            // GMO決済画面へリダイレクト
            return redirect($execResult['StartURL']);
        } catch (\Exception $e) {
            Log::error('GMO EntryTran/ExecTran Exception (Authenticated User)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()
                ->route('user.payment.index', ['id' => $selected->id])
                ->with('error', '決済処理中にエラーが発生しました。もう一度お試しください。');
        }
    }
}

