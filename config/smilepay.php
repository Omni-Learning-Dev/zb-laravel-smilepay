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
    | Callback URLs
    |--------------------------------------------------------------------------
    |
    | ZB SmilePay calls result_url server-to-server and redirects the customer
    | to return_url / cancel_url / failure_url after the hosted checkout.
    |
    | These default automatically to the package's own endpoints:
    |   result_url  → /smilepay/webhook  (handles server-to-server notification)
    |   return_url  → /smilepay/return   (handles user redirect)
    |   cancel_url  → /smilepay/return
    |   failure_url → /smilepay/return
    |
    | You only need to set these if you want to override the defaults.
    |
    */
    'return_url' => env('SMILEPAY_RETURN_URL', null),
    'result_url' => env('SMILEPAY_RESULT_URL', null),
    'cancel_url' => env('SMILEPAY_CANCEL_URL', null),
    'failure_url' => env('SMILEPAY_FAILURE_URL', null),

    /*
    |--------------------------------------------------------------------------
    | App Return URL (optional)
    |--------------------------------------------------------------------------
    |
    | After the package's /smilepay/return route processes the payment status,
    | it will redirect here with ?status=success|failed|cancelled&orderReference=...
    | appended. Useful for redirecting back to a mobile app deep-link, e.g.:
    |   SMILEPAY_APP_RETURN_URL=errandrunner://payments/return
    |
    | Leave null to return a JSON response instead (useful for SPAs/APIs).
    |
    */
    'app_return_url' => env('SMILEPAY_APP_RETURN_URL', null),

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
