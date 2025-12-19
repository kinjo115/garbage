<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // cURL SSL/TLS定数の互換性対応
        // CURL_SSLVERSION_TLSv1_2が未定義の場合（一部の環境で発生する問題を回避）
        if (extension_loaded('curl') && !defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6);
        }
    }
}
