<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserInfo;
use App\Http\Requests\UserInfoRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserInfoController extends Controller
{
    /**
     * 会員情報変更画面を表示（guest.register.confirmと同じブレードを使用）
     */
    public function editProfile()
    {
        $user = Auth::user();
        $userInfo = $user->userInfo;

        // UserInfoが存在しない場合は作成
        if (!$userInfo) {
            $userInfo = UserInfo::create([
                'user_id' => $user->id,
                'last_name' => '',
                'first_name' => '',
            ]);
        }

        // TempUserControllerと同じブレードを使用するため、$tempUser形式で渡す
        // ただし、実際にはUserオブジェクトなので、互換性のために適切に処理
        $tempUser = (object)[
            'token' => null, // 認証済みユーザーなのでトークンは不要
            'email' => $user->email,
            'userInfo' => $userInfo,
        ];

        return view('user.infos.register_confirmed', compact('tempUser'));
    }

    /**
     * 会員情報を更新（guest.register.confirm.storeと同じロジック）
     */
    public function updateProfile(UserInfoRequest $request)
    {
        $user = Auth::user();
        $userInfo = $user->userInfo;

        if (!$userInfo) {
            return redirect()->route('user.info.edit')
                ->with('error', 'ユーザー情報が見つかりませんでした。');
        }

        // バリデーション済みデータを取得
        $validated = $request->validated();

        // メールアドレスの検証（現在のユーザーのemailと一致することを確認）
        if ($validated['email'] !== $user->email) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'メールアドレスが登録時のものと一致しません。');
        }

        try {
            DB::beginTransaction();

            // UserInfoを更新
            $userInfo->update([
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
            ]);

            DB::commit();

            DB::commit();

            // 会員情報更新後、地図登録画面にリダイレクト（TempUserと同じフロー）
            return redirect()->route('user.info.map')
                ->with('success', '会員情報を更新しました。次に地図登録を行ってください。');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('会員情報更新エラー: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '会員情報の更新に失敗しました。もう一度お試しください。');
        }
    }

    /**
     * 地図登録画面を表示（TempUserController::storeRegisterConfirmedMapと同じロジック）
     */
    public function editMap()
    {
        $user = Auth::user();
        $userInfo = $user->userInfo;

        if (!$userInfo) {
            return redirect()->route('user.info.edit')
                ->with('error', '会員情報が登録されていません。最初からやり直してください。');
        }

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

        // TempUserControllerと同じブレードを使用するため、$tempUser形式で渡す
        $tempUser = (object)[
            'token' => null, // 認証済みユーザーなのでトークンは不要
            'email' => $user->email,
            'userInfo' => $userInfo,
        ];

        return view('user.infos.register_confirmed_map', compact('tempUser', 'address'));
    }

    /**
     * 地図位置情報を保存（TempUserController::storeMapLocationと同じロジック）
     */
    public function updateMap(Request $request)
    {
        $user = Auth::user();
        $userInfo = $user->userInfo;

        if (!$userInfo) {
            return redirect()->route('user.info.edit')
                ->with('error', 'ユーザー情報が見つかりません。最初からやり直してください。');
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

            DB::commit();

            return redirect()->route('user.mypage')
                ->with('success', '地図登録が完了しました。');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('地図位置情報保存エラー: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '地図登録に失敗しました。もう一度お試しください。');
        }
    }

    /**
     * 場所の指定ができない場合の処理（TempUserController::cancelMapLocationと同じロジック）
     */
    public function cancelMap(Request $request)
    {
        $user = Auth::user();
        $userInfo = $user->userInfo;

        if (!$userInfo) {
            return redirect()->route('user.info.edit')
                ->with('error', 'ユーザー情報が見つかりませんでした。');
        }

        try {
            DB::beginTransaction();

            // UserInfoの位置情報をnullに設定
            $userInfo->update([
                'home_latitude' => null,
                'home_longitude' => null,
                'disposal_latitude' => null,
                'disposal_longitude' => null,
                'apply_after_building' => false,
            ]);

            DB::commit();

            return redirect()->route('user.mypage')
                ->with('info', '場所の指定ができない場合は、電話でお申し込みください。フリーダイヤル：0120-758-530');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('場所の指定キャンセルエラー: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', '処理に失敗しました。もう一度お試しください。');
        }
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
