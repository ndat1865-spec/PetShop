<?php

return [

    'google' => ['client_id' => env('GOOGLE_CLIENT_ID'), 'client_secret' => env('GOOGLE_CLIENT_SECRET'), 'redirect' => env('GOOGLE_REDIRECT_URI'), 'guzzle' => ['verify' => env('SOCIALITE_SSL_VERIFY', true)]],
    'facebook' => ['client_id' => env('FACEBOOK_CLIENT_ID'), 'client_secret' => env('FACEBOOK_CLIENT_SECRET'), 'redirect' => env('FACEBOOK_REDIRECT_URI'), 'guzzle' => ['verify' => env('SOCIALITE_SSL_VERIFY', true)]],

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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
        'from' => env('RESEND_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'onboarding@resend.dev')),
        'from_name' => env('RESEND_FROM_NAME', env('MAIL_FROM_NAME', 'Pawora')),
        'ssl_verify' => env('RESEND_SSL_VERIFY', true),
    ],

    'vnpay' => [
        'enabled' => env('VNPAY_ENABLED', false),
        'tmn_code' => env('VNPAY_TMN_CODE'),
        'hash_secret' => env('VNPAY_HASH_SECRET'),
        'payment_url' => env('VNPAY_PAYMENT_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => env('VNPAY_RETURN_URL'),
    ],

    'zalopay' => [
        'enabled' => env('ZALOPAY_ENABLED', false),
        'app_id' => env('ZALOPAY_APP_ID'),
        'key1' => env('ZALOPAY_KEY1'),
        'key2' => env('ZALOPAY_KEY2'),
        'create_url' => env('ZALOPAY_CREATE_URL', 'https://sb-openapi.zalopay.vn/v2/create'),
        'callback_url' => env('ZALOPAY_CALLBACK_URL'),
        'redirect_url' => env('ZALOPAY_REDIRECT_URL'),
        'ssl_verify' => env('ZALOPAY_SSL_VERIFY', true),
    ],

    'momo' => [
        'enabled' => env('MOMO_ENABLED', false),
        'partner_code' => env('MOMO_PARTNER_CODE'),
        'access_key' => env('MOMO_ACCESS_KEY'),
        'secret_key' => env('MOMO_SECRET_KEY'),
        'partner_name' => env('MOMO_PARTNER_NAME', 'Pawora'),
        'store_id' => env('MOMO_STORE_ID', 'PaworaStore'),
        'request_type' => env('MOMO_REQUEST_TYPE', 'payWithMethod'),
        'create_url' => env('MOMO_CREATE_URL', 'https://test-payment.momo.vn/v2/gateway/api/create'),
        'redirect_url' => env('MOMO_REDIRECT_URL'),
        'ipn_url' => env('MOMO_IPN_URL'),
        'timeout' => env('MOMO_TIMEOUT', 30),
        'ssl_verify' => env('MOMO_SSL_VERIFY', true),
    ],

    'sepay' => [
        'bank' => env('SEPAY_BANK'),
        'account_number' => env('SEPAY_ACCOUNT_NUMBER'),
        'account_name' => env('SEPAY_ACCOUNT_NAME'),
        'webhook_api_key' => env('SEPAY_WEBHOOK_API_KEY'),
    ],

    'onepay' => [
        'enabled' => env('ONEPAY_ENABLED', false),
        'merchant_id' => env('ONEPAY_MERCHANT_ID'),
        'access_code' => env('ONEPAY_ACCESS_CODE'),
        'secure_secret' => env('ONEPAY_SECURE_SECRET'),
        'payment_url' => env('ONEPAY_PAYMENT_URL', 'https://mtf.onepay.vn/vpcpay/vpcpay.op'),
        'return_url' => env('ONEPAY_RETURN_URL'),
        'callback_url' => env('ONEPAY_CALLBACK_URL'),
    ],

];
