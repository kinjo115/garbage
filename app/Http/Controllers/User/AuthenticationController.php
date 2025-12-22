<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
}
