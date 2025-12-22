<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Jobs\SendMessageJob;
use App\Models\User;
use App\Models\TempUser;
use Carbon\Carbon;

class AuthenticationController extends Controller
{
    public function create(Request $request)
    {
        $email = $request->input('email');
        $phone = $request->input('phone');
        return view('user.auth.login', compact('email', 'phone'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'phone' => 'required|string|max:11',
        ]);

        // Only allow regular users (not admins) to login via this route
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->route('user.login')->with('error', __('auth.failed'));
        }

        if ($user->role === User::ROLE['ADMIN']) {
            // Admins should use /admin/login (Fortify)
            return redirect()->route('admin.login')->with('error', '管理者は管理画面からログインしてください。');
        }

        // Check if user has UserInfo and phone_number matches
        $userInfo = $user->userInfo;
        if (!$userInfo || $userInfo->phone_number !== $request->phone) {
            return redirect()->route('user.login')->with('error', '電話番号が正しくありません。');
        }

        // Authenticate with email and password
        if (Auth::attempt($request->only('email', 'password'))) {
            return redirect()->route('user.mypage');
        }

        return redirect()->route('user.login')->with('error', __('auth.failed'));
    }

    public function createRegister()
    {
        return view('user.auth.register');
    }

    public function storeRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'privacy_policy' => 'required',
        ]);


        try {
            DB::beginTransaction();

            $tempUser = TempUser::create([
                'email' => $request->email,
                'token' => Str::lower(Str::random(64)),
                'expires_at' => Carbon::now()->addHours(24)->toDateTimeString(),
                'status' => 0,
            ]);

            // Dispatch job after transaction commits (Laravel 12 best practice)
            SendMessageJob::dispatch($tempUser->email, '新規申込みの受付が完了しました', 'mails.user.auth.register.mail', [
                'email' => $tempUser->email,
                'token' => $tempUser->token,
                'expires_at' => $tempUser->expires_at,
            ])->afterCommit();

            DB::commit();

            return view('user.auth.register_created', compact('tempUser'))->with('success', __('messages.register_created'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('guest.register')
                ->with('error', __('messages.register_failed'));
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'ログアウトしました。');
    }

    /**
     * パスワードリセットリクエスト画面を表示
     */
    public function createForgotPassword(Request $request)
    {
        $email = $request->input('email');
        $phone = $request->input('phone');
        return view('user.auth.forgot-password', compact('email', 'phone'));
    }

    /**
     * パスワードリセットリクエストを処理
     */
    public function storeForgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone_number' => 'required|string|max:11',
        ]);

        // ユーザーを検索
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // セキュリティのため、ユーザーが存在しない場合でも成功メッセージを返す
            return redirect()->route('user.forgot-password')
                ->with('success', 'メールアドレスと電話番号が正しければ、パスワードリセット用のメールを送信しました。');
        }

        // 管理者は除外
        if ($user->role === User::ROLE['ADMIN']) {
            return redirect()->route('user.forgot-password')
                ->with('error', '管理者は管理画面からパスワードリセットを行ってください。');
        }

        // UserInfoと電話番号を確認
        $userInfo = $user->userInfo;
        if (!$userInfo || $userInfo->phone_number !== $request->phone_number) {
            // セキュリティのため、電話番号が一致しない場合でも成功メッセージを返す
            return redirect()->route('user.forgot-password')
                ->with('success', 'メールアドレスと電話番号が正しければ、パスワードリセット用のメールを送信しました。');
        }

        try {
            DB::beginTransaction();

            // パスワードリセットトークンを生成
            $token = Str::random(64);
            $expiresAt = Carbon::now()->addHours(1); // 1時間有効

            // 既存のトークンを削除
            DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->delete();

            // 新しいトークンを保存
            DB::table('password_reset_tokens')->insert([
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]);

            // メール送信
            $resetUrl = route('user.reset-password', ['token' => $token]);
            SendMessageJob::dispatch(
                $user->email,
                'パスワードリセット',
                'mails.user.auth.password_reset',
                [
                    'user' => $user,
                    'resetUrl' => $resetUrl,
                    'expiresAt' => $expiresAt,
                ]
            )->afterCommit();

            DB::commit();

            return redirect()->route('user.forgot-password')
                ->with('success', 'メールアドレスにパスワードリセット用のリンクを送信しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Password reset request failed: ' . $e->getMessage());

            return redirect()->route('user.forgot-password')
                ->with('error', 'パスワードリセットリクエストの処理に失敗しました。しばらくしてから再度お試しください。');
        }
    }

    /**
     * パスワードリセット画面を表示
     */
    public function createResetPassword(Request $request, $token)
    {
        // トークンの有効性を確認
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('created_at', '>', Carbon::now()->subHour())
            ->get()
            ->first(function ($record) use ($token) {
                return Hash::check($token, $record->token);
            });

        if (!$tokenRecord) {
            return redirect()->route('user.forgot-password')
                ->with('error', 'このリンクは無効または期限切れです。再度パスワードリセットをリクエストしてください。');
        }

        return view('user.auth.reset-password', [
            'token' => $token,
            'email' => $tokenRecord->email,
        ]);
    }

    /**
     * パスワードをリセット
     */
    public function storeResetPassword(Request $request, $token)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => ['required', 'string', 'min:8', 'regex:/^[a-zA-Z0-9]+$/', 'confirmed'],
        ], [
            'password.required' => 'パスワードを入力してください。',
            'password.min' => 'パスワードは8文字以上入力してください。',
            'password.regex' => 'パスワードは半角英数で入力してください。',
            'password.confirmed' => 'パスワード（確認）が一致しません。',
        ]);

        // トークンの有効性を確認
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('created_at', '>', Carbon::now()->subHour())
            ->get()
            ->first(function ($record) use ($token) {
                return Hash::check($token, $record->token);
            });

        if (!$tokenRecord) {
            return redirect()->route('user.forgot-password')
                ->with('error', 'このリンクは無効または期限切れです。再度パスワードリセットをリクエストしてください。');
        }

        // ユーザーを検索
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->route('user.forgot-password')
                ->with('error', 'ユーザーが見つかりません。');
        }

        try {
            DB::beginTransaction();

            // パスワードを更新
            $user->password = Hash::make($request->password);
            $user->save();

            // トークンを削除
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            DB::commit();

            return redirect()->route('user.login')
                ->with('success', 'パスワードをリセットしました。新しいパスワードでログインしてください。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Password reset failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'パスワードリセットに失敗しました。しばらくしてから再度お試しください。')
                ->withInput();
        }
    }
}
