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
        $tempUser = TempUser::where('token', $token)->where('expires_at', '>', now())->where('status', 0)->first();

        if (!$tempUser) {
            return abort(404);
        }

        return view('user.temp_user.register_confirmed', compact('tempUser'));
    }

    /**
     * 新規申込み情報を保存
     */
    public function storeRegisterConfirmed(UserInfoRequest $request, $token)
    {
        // TempUserの検証
        $tempUser = TempUser::where('token', $token)
            ->where('expires_at', '>', now())
            ->where('status', 0)
            ->first();

        if (!$tempUser) {
            return redirect()->route('user.register')
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


            // パスワード通知メールを送信（必要に応じて）
            // SendMessageJob::dispatch($user->email, 'アカウント登録が完了しました', 'mails.user.auth.password_notification', [
            //     'email' => $user->email,
            //     'password' => $temporaryPassword,
            // ])->afterCommit();

            DB::commit();

            return redirect()->route('user.register.confirm.store.map', ['token' => $token])
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
            return redirect()->route('user.register.confirm', ['token' => $token])
                ->with('error', '新規申込みの登録が完了していません。最初からやり直してください。');
        }

        return view('user.temp_user.register_confirmed_map', compact('tempUser'));
    }

    /**
     * 地図位置情報を保存
     */
    public function storeMapLocation(Request $request, $token)
    {
        $tempUser = TempUser::where('token', $token)->first();

        if (!$tempUser) {
            return redirect()->route('user.register')
                ->with('error', '無効なトークンです。');
        }

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
                return redirect()->route('user.register.confirm', ['token' => $token])
                    ->with('error', 'ユーザー情報が見つかりません。最初からやり直してください。');
            }

            $userInfo->update([
                'home_latitude' => $request->input('home_latitude'),
                'home_longitude' => $request->input('home_longitude'),
                'disposal_latitude' => $request->input('disposal_latitude'),
                'disposal_longitude' => $request->input('disposal_longitude'),
                'apply_after_building' => $request->has('apply_after_building'),
            ]);

            DB::commit();

            // 次のステップ（品目入力画面）へリダイレクト
            return redirect()->route('user.register.confirm.items', ['token' => $token])
                ->with('success', '地図登録が完了しました。');
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
}
