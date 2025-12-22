<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SelectedItem;

class WithdrawController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        // 未完了の申込みをチェック（キャンセルされていない、かつ決済が完了していない）
        $hasPendingApplications = SelectedItem::where('user_id', $user->id)
            ->where('confirm_status', '!=', SelectedItem::CONFIRM_STATUS_CANCELLED)
            ->where('payment_status', '!=', 2) // 2 = paid
            ->exists();

        return view('user.withdraw.edit', compact('hasPendingApplications'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // 未完了の申込みをチェック（キャンセルされていない、かつ決済が完了していない）
        $hasPendingApplications = SelectedItem::where('user_id', $user->id)
            ->where('confirm_status', '!=', SelectedItem::CONFIRM_STATUS_CANCELLED)
            ->where('payment_status', '!=', 2) // 2 = paid
            ->exists();

        if ($hasPendingApplications) {
            return redirect()
                ->route('user.withdraw.edit')
                ->with('error', '未完了の申込みがあるため、退会できません。すべての申込みを完了またはキャンセルしてから再度お試しください。');
        }

        try {
            DB::beginTransaction();

            // ユーザーに関連するデータを削除
            // UserInfoを削除（soft delete）
            if ($user->userInfo) {
                $user->userInfo->delete();
            }

            // SelectedItemのuser_idをnullに設定（履歴を保持するため）
            // または削除する場合は以下をコメントアウト
            // \App\Models\SelectedItem::where('user_id', $user->id)->delete();

            // ユーザーを削除
            $user->email = $user->email . '_' . time();
            $user->save();
            $user->delete();

            DB::commit();

            // ログアウト
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Log::info('User account deleted', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return redirect()
                ->route('home')
                ->with('success', '退会処理が完了しました。ご利用ありがとうございました。');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('User withdrawal error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('user.withdraw.edit')
                ->with('error', '退会処理中にエラーが発生しました。もう一度お試しください。');
        }
    }
}
