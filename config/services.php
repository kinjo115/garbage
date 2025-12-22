<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // 日本郵便API設定
    'japan_post' => [
        'api_url' => env('JAPAN_POST_API_URL', 'https://stub-qz73x.da.pf.japanpost.jp/api/v1'),
        'client_id' => env('JAPAN_POST_CLIENT_ID', 'Biz_DaPfJapanpost_MockAPI_j3QKS'),
        'secret_key' => env('JAPAN_POST_SECRET_KEY', 'uXuN0ejHG7nAn89AfAwa'),
    ],

    // Google Maps API設定
    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    // GMOペイメント設定
    'gmo_payment' => (function () {
        $apiUrl = env('GMO_PAYMENT_API_URL', 'https://pt01.mul-pay.jp/payment');
        return [
            'site_id' => env('GMO_PAYMENT_SITE_ID'),
            'site_pass' => env('GMO_PAYMENT_SITE_PASS'),
            'shop_id' => env('GMO_PAYMENT_SHOP_ID'),
            'shop_pass' => env('GMO_PAYMENT_SHOP_PASS'),
            'config_id' => env('GMO_PAYMENT_CONFIG_ID', '001'),
            'template_no' => env('GMO_PAYMENT_TEMPLATE_NO', '1'),
            'api_url' => $apiUrl,
            'get_linkplus_url' => env('GMO_PAYMENT_GET_LINKPLUS_URL', rtrim($apiUrl, '/') . '/GetLinkplusUrlPayment.json'),
            'entry_url' => $apiUrl . '/EntryTran',
            'exec_url' => $apiUrl . '/ExecTran',
            'return_url' => env('GMO_PAYMENT_RETURN_URL', '/user/payment/callback'),
            'cancel_url' => env('GMO_PAYMENT_CANCEL_URL', '/user/payment/cancel'),
        ];
    })(),

];
