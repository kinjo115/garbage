<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Define cURL SSL/TLS constants if not already defined (fix for Guzzle compatibility)
if (extension_loaded('curl')) {
    if (!defined('CURL_SSLVERSION_TLSv1_2')) {
        define('CURL_SSLVERSION_TLSv1_2', 6);
    }
    if (!defined('CURL_SSLVERSION_TLSv1_3')) {
        define('CURL_SSLVERSION_TLSv1_3', 7);
    }
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
