<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // CSRF exemption for payment callbacks (external payment gateway)
        $middleware->validateCsrfTokens(except: [
            'guest/payment/complete/token/*',
            'user/payment/complete/*',
            'gmo/payment/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Redirect unauthenticated users to user/login for user/* routes
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('user/*')) {
                return redirect()->route('user.login');
            }
        });
    })
    ->withSchedule(function (Schedule $schedule): void {

    })
    ->create();
