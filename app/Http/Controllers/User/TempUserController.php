<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TempUser;
use App\Models\UserInfo;
use App\Http\Requests\UserInfoRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TempUserController extends Controller
{
    public function confirmRegister($token)
    {
        $tempUser = TempUser::where('token', $token)->first();
        if (!$tempUser) {
            return abort(404);
        }

        return view('user.infos.register_confirmed', compact('tempUser'));
    }

    /**
     * 新規申込み情報を保存
     */
    public function storeRegisterConfirmed(UserInfoRequest $request, $token)
    {
        // TempUserの検証
        $tempUser = TempUser::where('token', $token)
            ->first();

        if (!$tempUser) {
            return redirect()->route('guest.register')
                ->with('error', '無効なトークンまたは期限切れです。');
        }

        // バリデーション済みデータを取得
        $validated = $request->validated();

        // メールアドレスの検証（TempUserのemailと一致することを確認）
        if ($validated['email'] !== $tempUser->email) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'メールアドレスが登録時のものと一致しません。');
        }

        try {
            DB::beginTransaction();

            // UserInfoを作成または更新
            $userInfo = UserInfo::updateOrCreate(
                [
                    'temp_user_id' => $tempUser->id,
                ],
                [
                    'temp_user_id' => $tempUser->id,
                    'last_name' => $validated['last_name'],
                    'first_name' => $validated['first_name'],
                    'housing_type_id' => $validated['housing_type_id'],
                    'postal_code' => str_replace('-', '', $validated['postal_code']), // ハイフンを削除
                    'prefecture_id' => $validated['prefecture_id'],
                    'city' => $validated['city'],
                    'town' => $validated['town'],
                    'chome' => $validated['chome'] ?? null,
                    'building_number' => $validated['building_number'] ?? null,
                    'house_number' => $validated['house_number'] ?? null,
                    'apartment_name' => $validated['apartment_name'] ?? null,
                    'apartment_number' => $validated['apartment_number'] ?? null,
                    'phone_number' => str_replace('-', '', $validated['phone_number']), // ハイフンを削除
                    'emergency_contact' => str_replace('-', '', $validated['emergency_contact']), // ハイフンを削除
                ]
            );

            // TempUserのステータスを更新（登録完了）
            $tempUser->update([
                'status' => 1, // 1: 登録完了
            ]);

            // パスワード通知メールを送信（必要に応じて）
            // SendMessageJob::dispatch($user->email, 'アカウント登録が完了しました', 'mails.user.auth.password_notification', [
            //     'email' => $user->email,
            //     'password' => $temporaryPassword,
            // ])->afterCommit();

            DB::commit();

            return redirect()->route('guest.register.confirm.store.map', ['token' => $token])
                ->with('success', '登録が完了しました。');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('ユーザー情報登録エラー: ' . $e->getMessage(), [
                'token' => $token,
                'email' => $request->input('email', $tempUser->email ?? 'unknown'),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '登録に失敗しました。もう一度お試しください。');
        }
    }

    public function storeRegisterConfirmedMap($token)
    {
        $tempUser = TempUser::where('token', $token)->first();

        if (!$tempUser) {
            return abort(404);
        }

        if (!$tempUser->userInfo) {
            return redirect()->route('guest.register.confirm', ['token' => $token])
                ->with('error', '新規申込みの登録が完了していません。最初からやり直してください。');
        }

        $userInfo = $tempUser->userInfo;

        // 住所情報を取得（地図の初期表示用）
        $addressParts = [];

        if ($userInfo->postal_code) {
            $addressParts[] = $userInfo->postal_code;
        }

        // 都道府県名を取得（リレーションを使用）
        if ($userInfo->prefecture) {
            $addressParts[] = $userInfo->prefecture->name;
        }

        if ($userInfo->city) {
            $addressParts[] = $userInfo->city;
        }

        if ($userInfo->town) {
            $addressParts[] = $userInfo->town;
        }

        $address = implode(' ', $addressParts);

        return view('user.infos.register_confirmed_map', compact('tempUser', 'address'));
    }

    /**
     * 地図位置情報を保存（品目入力に進む）
     */
    public function storeMapLocation(Request $request, $token)
    {
        $tempUser = TempUser::where('token', $token)->first();

        if (!$tempUser) {
            return redirect()->route('guest.register')
                ->with('error', '無効なトークンです。');
        }

        // バリデーション
        $request->validate([
            'home_latitude' => 'nullable|numeric|between:-90,90',
            'home_longitude' => 'nullable|numeric|between:-180,180',
            'disposal_latitude' => 'nullable|numeric|between:-90,90',
            'disposal_longitude' => 'nullable|numeric|between:-180,180',
            'apply_after_building' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $userInfo = UserInfo::where('temp_user_id', $tempUser->id)->first();

            if (!$userInfo) {
                return redirect()->route('guest.register.confirm', ['token' => $token])
                    ->with('error', 'ユーザー情報が見つかりません。最初からやり直してください。');
            }

            // 建物が建ってから初めて申し込む場合のチェック
            $applyAfterBuilding = $request->has('apply_after_building') && $request->input('apply_after_building') == '1';

            // 建物が建ってから初めて申し込む場合以外は、位置情報が必須
            if (!$applyAfterBuilding) {
                if (empty($request->input('home_latitude')) || empty($request->input('home_longitude'))) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', '自宅位置を設定してください。');
                }

                if (empty($request->input('disposal_latitude')) || empty($request->input('disposal_longitude'))) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', '排出位置を設定してください。');
                }

                // 距離チェック（500m以上の場合エラー）
                $homeLat = (float)$request->input('home_latitude');
                $homeLng = (float)$request->input('home_longitude');
                $disposalLat = (float)$request->input('disposal_latitude');
                $disposalLng = (float)$request->input('disposal_longitude');

                $distance = $this->calculateDistance($homeLat, $homeLng, $disposalLat, $disposalLng);

                if ($distance > 500) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "自宅と排出位置が" . round($distance) . "m離れています。距離が500m以上の場合、送信できません。位置を調整してください。");
                }
            }

            // 位置情報を更新
            $userInfo->update([
                'home_latitude' => $request->input('home_latitude'),
                'home_longitude' => $request->input('home_longitude'),
                'disposal_latitude' => $request->input('disposal_latitude'),
                'disposal_longitude' => $request->input('disposal_longitude'),
                'apply_after_building' => $applyAfterBuilding,
            ]);

            // TempUserのステータスを更新（地図登録完了）
            $tempUser->update([
                'status' => 2, // 2: 地図登録完了
            ]);

            DB::commit();

            return redirect()->route('guest.item.index', ['token' => $token])
                ->with('success', '地図登録が完了しました。品目入力画面は準備中です。');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('地図位置情報保存エラー: ' . $e->getMessage(), [
                'token' => $token,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '地図登録に失敗しました。もう一度お試しください。');
        }
    }

    /**
     * 場所の指定ができない場合の処理
     */
    public function cancelMapLocation(Request $request, $token)
    {
        $tempUser = TempUser::where('token', $token)->first();

        if (!$tempUser) {
            return redirect()->route('guest.register')
                ->with('error', '無効なトークンです。');
        }

        try {
            DB::beginTransaction();

            // UserInfoの位置情報をnullに設定
            $userInfo = UserInfo::where('temp_user_id', $tempUser->id)->first();
            if ($userInfo) {
                $userInfo->update([
                    'home_latitude' => null,
                    'home_longitude' => null,
                    'disposal_latitude' => null,
                    'disposal_longitude' => null,
                    'apply_after_building' => false,
                ]);
            }

            // TempUserのステータスを更新（電話での申し込みが必要）
            $tempUser->update([
                'status' => 3, // 3: 電話での申し込みが必要
            ]);

            DB::commit();

            // 電話での申し込み案内ページにリダイレクト
            // 一時的にホームにリダイレクト（電話案内ページが作成されたら変更）
            return redirect()->route('home')
                ->with('info', '場所の指定ができない場合は、電話でお申し込みください。フリーダイヤル：0120-758-530');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('場所の指定キャンセルエラー: ' . $e->getMessage(), [
                'token' => $token,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', '処理に失敗しました。もう一度お試しください。');
        }
    }

    /**
     * 登録完了画面
     */
    public function complete($token)
    {
        $tempUser = TempUser::where('token', $token)->first();

        if (!$tempUser) {
            return abort(404);
        }

        return view('user.temp_user.register_complete', compact('tempUser'));
    }

    /**
     * 電話での申し込み案内画面
     */
    public function phone($token)
    {
        $tempUser = TempUser::where('token', $token)->first();

        if (!$tempUser) {
            return abort(404);
        }

        return view('user.temp_user.register_phone', compact('tempUser'));
    }

    /**
     * 2点間の距離を計算（Haversine formula）
     * 戻り値: メートル単位の距離
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // 地球の半径（メートル）

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
