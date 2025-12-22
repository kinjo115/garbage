<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendMessageJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserPasswordController extends Controller
{
    /**
     * パスワード変更画面を表示
     */
    public function edit()
    {
        return view('user.password.edit');
    }

    /**
     * パスワードを更新
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:8', 'regex:/^[a-zA-Z0-9]+$/', 'confirmed'],
        ], [
            'password.required' => 'パスワードを入力してください。',
            'password.min' => 'パスワードは8文字以上入力してください。',
            'password.regex' => 'パスワードは半角英数で入力してください。',
            'password.confirmed' => 'パスワード（確認）が一致しません。',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $userInfo = $user->userInfo;

        DB::beginTransaction();

        try {
            $user->password = Hash::make($request->password);
            $user->save();

            // ユーザー名を取得
            $userName = '';
            if ($userInfo) {
                $userName = trim(($userInfo->last_name ?? '') . ' ' . ($userInfo->first_name ?? ''));
            }
            if (empty($userName)) {
                $userName = $user->name ?? 'ユーザー';
            }

            // パスワード変更通知メールを送信
            SendMessageJob::dispatch(
                $user->email,
                'パスワードが変更されました',
                'mails.user.auth.password_changed',
                [
                    'name' => $userName,
                    'email' => $user->email,
                    'changed_at' => Carbon::now()->format('Y年n月j日 H:i'),
                ]
            )->afterCommit();

            DB::commit();

            return redirect()->route('user.password.edit')
                ->with('success', 'パスワードを変更しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'パスワードの変更に失敗しました。もう一度お試しください。')
                ->withInput();
        }
    }
}

