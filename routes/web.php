<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\User\AuthenticationController as UserAuthenticationController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('user')->name('user.')->middleware('guest')->group(function () {
    Route::get('login', [UserAuthenticationController::class, 'create'])->name('login');
    Route::post('login', [UserAuthenticationController::class, 'store'])->name('login.store');

    Route::get('register', [UserAuthenticationController::class, 'createRegister'])->name('register');
    Route::post('register', [UserAuthenticationController::class, 'storeRegister'])->name('register.store');

    Route::get('register/token/{token}', [UserAuthenticationController::class, 'confirmRegisterCreate'])->name('register.confirm');
});

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

// Route::middleware(['auth'])->group(function () {
//     Route::redirect('settings', 'settings/profile');

//     Route::get('settings/profile', Profile::class)->name('profile.edit');
//     Route::get('settings/password', Password::class)->name('user-password.edit');
//     Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

//     Route::get('settings/two-factor', TwoFactor::class)
//         ->middleware(
//             when(
//                 Features::canManageTwoFactorAuthentication()
//                     && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
//                 ['password.confirm'],
//                 [],
//             ),
//         )
//         ->name('two-factor.show');
// });