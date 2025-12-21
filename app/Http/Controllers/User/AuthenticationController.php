<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Jobs\SendMessageJob;
use App\Models\TempUser;
use Carbon\Carbon;

class AuthenticationController extends Controller
{
    public function create()
    {
        return view('user.auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Only allow regular users (not admins) to login via this route
        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user && $user->role === \App\Models\User::ROLE['ADMIN']) {
            // Admins should use /admin/login (Fortify)
            return redirect()->route('admin.login')->with('error', '管理者は管理画面からログインしてください。');
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            return redirect()->route('home');
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
}
