<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\SelectedItem;
use App\Models\PaymentHistory;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\TempUser;
use App\Jobs\SendMessageJob;
use Carbon\Carbon;

class GmoPaymentController extends Controller
{
    public function callback(Request $request)
    {
        // コールバック受信をログに記録
        Log::info('GMO Callback Received', [
            'method' => $request->method(),
            'all_params' => $request->all(),
            'query_params' => $request->query(),
            'post_params' => $request->post(),
        ]);

        // GMOからのレスポンスパラメータを取得
        $orderId = $request->input('OrderID');
        $status = $request->input('Status');
        $accessId = $request->input('AccessID');
        $errCode = $request->input('ErrCode');
        $errInfo = $request->input('ErrInfo');
        $tranId = $request->input('TranID');
        $approve = $request->input('Approve');
        $tranDate = $request->input('TranDate');
        $amount = $request->input('Amount');
        $jobCd = $request->input('JobCd');

        // OrderIDからSelectedItemを検索
        $selected = null;
        if ($orderId) {
            $selected = SelectedItem::where('transaction_id', $orderId)->first();
        }

        if (!$selected) {
            Log::error('GMO Callback: SelectedItem not found', [
                'order_id' => $orderId,
                'request_params' => $request->all(),
            ]);

            // エラーでもPaymentHistoryに記録
            PaymentHistory::create([
                'selected_item_id' => 0, // 見つからない場合は0
                'order_id' => $orderId,
                'status' => $status,
                'err_code' => 'ITEM_NOT_FOUND',
                'err_info' => 'SelectedItem not found for OrderID: ' . $orderId,
                'raw_response' => $request->all(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        DB::beginTransaction();
        try {
            // 同じOrderIDで既にPaymentHistoryが存在するかチェック（重複防止）
            $existingPaymentHistory = PaymentHistory::where('order_id', $orderId)
                ->where('selected_item_id', $selected->id)
                ->first();

            if ($existingPaymentHistory) {
                Log::info('GMO Callback: PaymentHistory already exists', [
                    'OrderID' => $orderId,
                    'selected_item_id' => $selected->id,
                    'payment_history_id' => $existingPaymentHistory->id,
                ]);
                $paymentHistory = $existingPaymentHistory;
            } else {
                // PaymentHistoryを作成
                $paymentHistory = PaymentHistory::create([
                    'selected_item_id' => $selected->id,
                    'shop_id' => $request->input('ShopID'),
                    'access_id' => $accessId,
                    'order_id' => $orderId,
                    'status' => $status,
                    'job_cd' => $jobCd,
                    'amount' => $amount ? (int)$amount : null,
                    'tax' => $request->input('Tax') ? (int)$request->input('Tax') : null,
                    'currency' => $request->input('Currency', 'JPN'),
                    'forward' => $request->input('Forward'),
                    'method' => $request->input('Method'),
                    'pay_times' => $request->input('PayTimes') ? (int)$request->input('PayTimes') : null,
                    'tran_id' => $tranId,
                    'approve' => $approve,
                    'tran_date' => $tranDate,
                    'err_code' => $errCode,
                    'err_info' => $errInfo,
                    'pay_type' => $request->input('PayType'),
                    'raw_response' => $request->all(),
                ]);

                Log::info('GMO Callback: PaymentHistory created', [
                    'OrderID' => $orderId,
                    'selected_item_id' => $selected->id,
                    'payment_history_id' => $paymentHistory->id,
                ]);
            }

            // エラーがある場合
            if (!empty($errCode)) {
                Log::error('GMO Callback Error', [
                    'ErrCode' => $errCode,
                    'ErrInfo' => $errInfo,
                    'OrderID' => $orderId,
                    'selected_item_id' => $selected->id,
                ]);

                $selected->payment_status = 0; // not paid
                $selected->save();

                DB::commit();

                return response()->json([
                    'status' => 'error',
                    'err_code' => $errCode,
                    'err_info' => $errInfo,
                ], 400);
            }

            // 決済成功の判定
            if ($status === 'CAPTURE' || $status === 'AUTH') {
                // tempUserが存在する場合、ユーザーを作成（既に決済が完了している場合でも）
                $tempUser = $selected->tempUser;
                $user = $selected->user;

                if ($tempUser && !$user) {
                    // tempUserからユーザーを作成
                    $user = $this->makeUserFromTempUser($tempUser, $selected);
                    // ユーザー作成後、selectedを再取得（user_idが更新されている可能性があるため）
                    $selected->refresh();
                }

                // 既に決済が完了している場合は重複処理を防ぐ
                if ($selected->payment_status === 2 && $selected->payment_date) {
                    Log::info('GMO Callback: Payment already processed', [
                        'OrderID' => $orderId,
                        'selected_item_id' => $selected->id,
                        'user_id' => $selected->user_id,
                    ]);

                    DB::commit();
                    return response()->json(['status' => 'success', 'message' => 'Payment already processed']);
                }

                // 決済成功
                $selected->payment_status = 2; // paid
                $selected->payment_date = now();

                // 受付番号のシリアル番号を生成（まだない場合）
                if (!$selected->reception_number_serial) {
                    $selectedId = (string)$selected->id;
                    $last5Digits = substr($selectedId, -5);
                    $selected->reception_number_serial = str_pad($last5Digits, 5, '0', STR_PAD_LEFT);
                }

                $selected->save();

                // ユーザー情報を取得（メール送信用）
                if (!$user) {
                    $user = $selected->user;
                }

                if ($user || $tempUser) {
                    // 決済完了メールを送信
                    $userInfo = $user ? $user->userInfo : null;

                    // tempUserの場合、userInfoを取得
                    if ($tempUser && !$userInfo) {
                        $userInfo = UserInfo::where('temp_user_id', $tempUser->id)->first();
                    }

                    $userName = $user ? $user->name : ($tempUser && $userInfo ? trim(($userInfo->last_name ?? '') . ' ' . ($userInfo->first_name ?? '')) : '');
                    $email = $user ? $user->email : ($tempUser ? $tempUser->email : null);

                    if ($email) {
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
                        $yy = $paymentDate->format('y');
                        $mm = $paymentDate->format('m');
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
                    }
                }

                Log::info('GMO Payment Success', [
                    'OrderID' => $orderId,
                    'Status' => $status,
                    'AccessID' => $accessId,
                    'TranID' => $tranId,
                    'selected_item_id' => $selected->id,
                    'user_id' => $selected->user_id,
                    'email_sent' => true,
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment processed successfully',
                    'order_id' => $orderId,
                ]);
            } else {
                // 決済失敗または不明なステータス
                Log::warning('GMO Callback Unknown Status', [
                    'Status' => $status,
                    'OrderID' => $orderId,
                    'selected_item_id' => $selected->id,
                ]);

                $selected->payment_status = 0; // not paid
                $selected->save();

                DB::commit();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Unknown payment status',
                    'status_received' => $status,
                ], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('GMO Callback Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $orderId,
                'selected_item_id' => $selected->id ?? null,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * tempUserからユーザーを作成
     */
    private function makeUserFromTempUser($tempUser, $selected)
    {
        $userInfo = UserInfo::where('temp_user_id', $tempUser->id)->first();

        // UserInfoが存在しない場合はエラー
        if (!$userInfo) {
            Log::error('UserInfo not found for temp_user in GMO callback', [
                'temp_user_id' => $tempUser->id,
                'temp_user_email' => $tempUser->email,
            ]);
            return null;
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

                // SelectedItemをユーザーに紐付け（まだ紐付けられていない場合）
                if (!$selected->user_id) {
                    $selected->user_id = $user->id;
                }
            } else {
                // 新規ユーザーを作成
                try {
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
                    }

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

                    Log::info('User created from tempUser in GMO callback', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'temp_user_id' => $tempUser->id,
                        'selected_item_id' => $selected->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error creating user from tempUser in GMO callback', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'temp_user_id' => $tempUser->id,
                        'email' => $tempUser->email,
                    ]);
                    return null;
                }
            }
        }

        // 既存ユーザーの場合、SelectedItemのみ紐付け（まだ紐付けられていない場合）
        // 注意: この処理は既に上記で実行されているため、ここでは確認のみ
        if ($user && !$selected->user_id) {
            $selected->user_id = $user->id;
            Log::warning('SelectedItem user_id was not set in makeUserFromTempUser', [
                'selected_item_id' => $selected->id,
                'user_id' => $user->id,
            ]);
        }

        Log::info('makeUserFromTempUser completed', [
            'temp_user_id' => $tempUser->id,
            'user_id' => $user ? $user->id : null,
            'selected_item_id' => $selected->id,
            'selected_user_id' => $selected->user_id,
        ]);

        return $user;
    }
}
