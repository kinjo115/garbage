<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\User\AuthenticationController as UserAuthenticationController;
use App\Http\Controllers\User\TempUserController as UserTempUserController;
use App\Http\Controllers\User\TempUserItemController as UserTempUserItemController;
use App\Http\Controllers\User\TempUserPaymentController as UserTempUserPaymentController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\UserInfoController;
use App\Http\Controllers\User\HistoryController as UserHistoryController;


Route::get('/', [HomeController::class, 'index'])->name('home');

// Japan Post API proxy
Route::get('/api/japan-post/search', [App\Http\Controllers\Api\JapanPostController::class, 'searchAddress'])->name('api.japan-post.search');

Route::prefix('guest')->name('guest.')->middleware('guest')->group(function () {
    Route::get('register', [UserAuthenticationController::class, 'createRegister'])->name('register');
    Route::post('register', [UserAuthenticationController::class, 'storeRegister'])->name('register.store');

    Route::get('register/token/{token}', [UserTempUserController::class, 'confirmRegister'])->name('register.confirm');
    Route::post('register/token/{token}', [UserTempUserController::class, 'storeRegisterConfirmed'])->name('register.confirm.store');
    Route::get('register/token/{token}/map', [UserTempUserController::class, 'storeRegisterConfirmedMap'])->name('register.confirm.store.map');
    Route::post('register/token/{token}/map', [UserTempUserController::class, 'storeMapLocation'])->name('register.confirm.store.map.save');
    Route::post('register/token/{token}/map/cancel', [UserTempUserController::class, 'cancelMapLocation'])->name('register.confirm.store.map.cancel');

    Route::prefix('item')->name('item.')->group(function () {
        Route::get('token/{token}', [UserTempUserItemController::class, 'index'])->name('index');
        Route::post('token/{token}', [UserTempUserItemController::class, 'store'])->name('store');
    });

    Route::get('confirmation/token/{token}', [UserTempUserItemController::class, 'confirmationIndex'])->name('confirmation.index');
    Route::post('confirmation/token/{token}', [UserTempUserItemController::class, 'confirmationStore'])->name('confirmation.store');

    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('token/{token}', [UserTempUserPaymentController::class, 'index'])->name('index');
        Route::post('token/{token}', [UserTempUserPaymentController::class, 'store'])->name('store');
        Route::get('convenience/token/{token}', [UserTempUserPaymentController::class, 'convenience'])->name('convenience');
        Route::match(['get', 'post'], 'callback/token/{token}', [UserTempUserPaymentController::class, 'callback'])->name('callback');
        Route::get('cancel/token/{token}', [UserTempUserPaymentController::class, 'cancel'])->name('cancel');
        Route::get('complete/token/{token}', [UserTempUserPaymentController::class, 'complete'])->name('complete');
    });
});

// Custom user login (not using Fortify) - accessible to guests
Route::prefix('user')->name('user.')->middleware('guest')->group(function () {
    Route::get('login', [UserAuthenticationController::class, 'create'])->name('login');
    Route::post('login', [UserAuthenticationController::class, 'store'])->name('login.store');
});

// Admin routes using Fortify (only for admin users)
Route::prefix('admin')->name('admin.')->middleware('guest')->group(function () {
    // Fortify will handle these routes at /admin/login
});

// Authenticated user routes
Route::prefix('user')->name('user.')->middleware('auth')->group(function () {
    Route::get('mypage', [UserDashboardController::class, 'index'])->name('mypage');
    Route::post('logout', [UserAuthenticationController::class, 'logout'])->name('logout');

    Route::prefix('info')->name('info.')->group(function () {
        Route::get('edit', [UserInfoController::class, 'editProfile'])->name('edit');
        Route::post('edit', [UserInfoController::class, 'updateProfile'])->name('update');

        Route::get('map', [UserInfoController::class, 'editMap'])->name('map');
        Route::post('map', [UserInfoController::class, 'updateMap'])->name('map.update');
        Route::post('map/cancel', [UserInfoController::class, 'cancelMap'])->name('map.cancel');
    });

    Route::prefix('history')->name('history.')->group(function () {
        Route::get('index', [UserHistoryController::class, 'index'])->name('index');
        Route::get('show/{id}', [UserHistoryController::class, 'show'])->name('show');
        Route::post('cancel', [UserHistoryController::class, 'cancel'])->name('cancel');
    });

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
