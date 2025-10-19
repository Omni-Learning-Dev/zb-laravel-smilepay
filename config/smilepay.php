<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SmilePay Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the environment mode for SmilePay integration.
    | Supported: "sandbox", "production"
    |
    */
    'environment' => env('SMILEPAY_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Your SmilePay API credentials obtained from the merchant dashboard.
    | https://zbnet.zb.co.zw/wallet_sandbox_merchant/ (Sandbox)
    |
    */
    'api_key' => env('SMILEPAY_API_KEY'),
    'api_secret' => env('SMILEPAY_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | API Base URLs
    |--------------------------------------------------------------------------
    |
    | The base URLs for SmilePay API endpoints.
    |
    */
    'base_url' => [
        'sandbox' => 'https://zbnet.zb.co.zw/wallet_sandbox_api/payments-gateway/',
        'production' => 'https://zbnet.zb.co.zw/wallet_gateway/payments-gateway/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Checkout URLs
    |--------------------------------------------------------------------------
    |
    | The checkout page URLs for hosted payment pages.
    |
    */
    'checkout_url' => [
        'sandbox' => 'https://zbnet.zb.co.zw/wallet_sandbox_checkout',
        'production' => 'https://zbnet.zb.co.zw/wallet_checkout',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency code for transactions.
    | Supported: "840" (USD), "924" (ZWG)
    |
    */
    'default_currency' => env('SMILEPAY_DEFAULT_CURRENCY', '840'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    |
    | Configure webhook/callback settings for payment notifications.
    |
    */
    'webhook' => [
        'route_name' => 'smilepay.webhook',
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default URLs
    |--------------------------------------------------------------------------
    |
    | Default return, result, cancel, and failure URLs.
    |
    */
    'return_url' => env('SMILEPAY_RETURN_URL', null),
    'result_url' => env('SMILEPAY_RESULT_URL', null),
    'cancel_url' => env('SMILEPAY_CANCEL_URL', null),
    'failure_url' => env('SMILEPAY_FAILURE_URL', null),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    |
    | Configure HTTP client timeout and retry settings.
    |
    */
    'timeout' => env('SMILEPAY_TIMEOUT', 30),
    'connect_timeout' => env('SMILEPAY_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable request/response logging for debugging.
    |
    */
    'logging' => [
        'enabled' => env('SMILEPAY_LOGGING', false),
        'channel' => env('SMILEPAY_LOG_CHANNEL', 'stack'),
    ],
];
