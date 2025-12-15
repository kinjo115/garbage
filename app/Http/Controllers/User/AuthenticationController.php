<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Jobs\SendMessageJob;

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

        $password = Str::lower(Str::random(10));

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => explode('@', $request->email)[0],
                'email' => $request->email,
                'password' => Hash::make($password),
            ]);

            // Dispatch job after transaction commits (Laravel 12 best practice)
            SendMessageJob::dispatch($user->email, '新規申込みの受付が完了しました', 'mails.user.auth.register.mail', [
                'email' => $user->email,
                'password' => $password,
            ])->afterCommit();

            DB::commit();

            return redirect()->route('user.register')
                ->with('success', __('messages.register_success'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('user.register')
                ->with('error', __('messages.register_failed'));
        }
    }
}