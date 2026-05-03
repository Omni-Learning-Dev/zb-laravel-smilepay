# Laravel SmilePay Package

A comprehensive Laravel package for integrating **SmilePay Payment Gateway** (ZB Bank Zimbabwe) into your Laravel application. This package supports multiple payment methods including Ecocash, Innbucks, Omari, SmileCash, and Visa/Mastercard.

[![Latest Version](https://img.shields.io/packagist/v/emmanuelsiziba/zb-laravel-smilepay.svg?style=flat-square)](https://packagist.org/packages/emmanuelsiziba/zb-laravel-smilepay)
[![Total Downloads](https://img.shields.io/packagist/dt/emmanuelsiziba/zb-laravel-smilepay.svg?style=flat-square)](https://packagist.org/packages/emmanuelsiziba/zb-laravel-smilepay)
[![License](https://img.shields.io/packagist/l/emmanuelsiziba/zb-laravel-smilepay.svg?style=flat-square)](https://packagist.org/packages/emmanuelsiziba/zb-laravel-smilepay)

## Features

✅ **Multi-Payment Support** – Ecocash, Innbucks, Omari, SmileCash, Visa/Mastercard
✅ **Standard Checkout** – Hosted payment page integration
✅ **Express Checkout** – Direct API integration for custom UIs
✅ **Webhook Handling** – Automatic payment status callbacks
✅ **Auto-Configured Callbacks** – No manual URL setup required; the package registers and wires its own routes
✅ **Built-in Return Handler** – `/smilepay/return` polls status, fires events, and redirects the user
✅ **Dual-App Return URLs** – Route mobile deep-links and web redirects independently via a `platform` param
✅ **Transaction Management** – Check status, cancel payments
✅ **Event System** – Laravel events for payment notifications
✅ **Sandbox & Production** – Easy environment switching
✅ **Comprehensive Logging** – Debug and track all API interactions

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [How Callbacks Work](#how-callbacks-work)
- [App Return URLs](#app-return-urls)
- [Usage](#usage)
  - [Standard Checkout](#standard-checkout)
  - [Express Checkout](#express-checkout)
  - [Transaction Management](#transaction-management)
  - [Webhook Handling](#webhook-handling)
- [Events](#events)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [License](#license)

## Requirements

- PHP 8.0 or higher
- Laravel 9.x, 10.x, or 11.x
- Guzzle HTTP Client 7.x

## Installation

Install the package via Composer:

```bash
composer require emmanuelsiziba/zb-laravel-smilepay
```

### Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=smilepay-config
```

This will create a `config/smilepay.php` configuration file.

## Configuration

### Environment Variables

The minimum configuration needed is just your credentials:

```env
SMILEPAY_ENVIRONMENT=sandbox
SMILEPAY_API_KEY=your_api_key_here
SMILEPAY_API_SECRET=your_api_secret_here
SMILEPAY_DEFAULT_CURRENCY=840
```

The package automatically registers and wires its own callback routes (`/smilepay/webhook` and `/smilepay/return`), so **you do not need to set `SMILEPAY_RETURN_URL`, `SMILEPAY_RESULT_URL`, `SMILEPAY_CANCEL_URL`, or `SMILEPAY_FAILURE_URL`** unless you want to override the defaults.

#### Optional: App Return URLs

After the hosted checkout completes, the package's `/smilepay/return` route will redirect your user. Configure where to send them:

```env
# Single app (web or mobile)
SMILEPAY_APP_RETURN_URL=https://yourapp.com/payments/return

# OR — separate URLs for mobile deep-links and web redirects
SMILEPAY_MOBILE_RETURN_URL=yourapp://payments/return
SMILEPAY_WEB_RETURN_URL=https://yourapp.com/payments/return
```

If none of these are set, the `/smilepay/return` route returns a JSON response instead of redirecting.

#### Optional: Override Callback URLs

Only set these if you need to point to a custom handler instead of the package's built-in routes:

```env
SMILEPAY_RETURN_URL=https://yourdomain.com/custom/return
SMILEPAY_RESULT_URL=https://yourdomain.com/custom/webhook
SMILEPAY_CANCEL_URL=https://yourdomain.com/custom/cancel
SMILEPAY_FAILURE_URL=https://yourdomain.com/custom/failed
```

#### Logging (optional)

```env
SMILEPAY_LOGGING=true
SMILEPAY_LOG_CHANNEL=stack
```

### Currency Codes

- `840` - USD (United States Dollar)
- `924` - ZWG (Zimbabwean Dollar)

### Getting API Credentials

1. **Sandbox**: Register at [https://zbnet.zb.co.zw/wallet_sandbox_merchant/](https://zbnet.zb.co.zw/wallet_sandbox_merchant/)
2. Navigate to **Settings > API Keys**
3. Click **Generate New API Key**
4. Copy your API Key and API Secret

## How Callbacks Work

When the package is installed, its service provider automatically:

1. Registers two routes in your application:
   - `POST /smilepay/webhook` — ZB calls this server-to-server to notify you of a payment result
   - `GET  /smilepay/return`  — ZB redirects the customer here after the hosted checkout page

2. Populates `returnUrl`, `resultUrl`, `cancelUrl`, and `failureUrl` on every payment request using `url('/smilepay/return')` and `url('/smilepay/webhook')` as defaults, so you never need to set them manually.

### What `/smilepay/return` Does

When ZB redirects the customer back to your server, this built-in controller:

1. Reads the `orderReference` from the query string
2. Polls ZB for the definitive payment status
3. Fires the appropriate package event (`PaymentReceived`, `PaymentFailed`, or `PaymentCanceled`)
4. Redirects the user to your configured app return URL (with `?status=&orderReference=` appended), or returns JSON if no URL is configured

---

## App Return URLs

Use app return URLs to send the customer back to your application after the hosted checkout.

### Single Application

```env
SMILEPAY_APP_RETURN_URL=https://yourapp.com/payments/return
```

The customer will be redirected to:
```
https://yourapp.com/payments/return?status=success&orderReference=SP-ABC123
```

### Mobile App + Web App (Dual Platform)

If you serve both a mobile app (deep link) and a web app, configure separate URLs:

```env
SMILEPAY_MOBILE_RETURN_URL=yourapp://payments/return
SMILEPAY_WEB_RETURN_URL=https://yourapp.com/payments/return
```

Then signal the platform when initiating a payment by appending `?platform=mobile` or `?platform=web` to the `returnUrl`, `cancelUrl`, and `failureUrl`:

```php
$platform = 'mobile'; // or 'web', determined by your request context

SmilePay::standardCheckout()->initiate([
    'amount'            => 100.00,
    'itemName'          => 'Order #123',
    'returnUrl'         => url('/smilepay/return') . '?platform=' . $platform,
    'cancelUrl'         => url('/smilepay/return') . '?platform=' . $platform,
    'failureUrl'        => url('/smilepay/return') . '?platform=' . $platform,
    // resultUrl is always server-to-server — no platform param needed
]);
```

The `ReturnController` reads `?platform=` and picks the matching configured URL:

| `?platform=` | Uses config key | `.env` variable |
|---|---|---|
| `mobile` | `smilepay.mobile_return_url` | `SMILEPAY_MOBILE_RETURN_URL` |
| `web` | `smilepay.web_return_url` | `SMILEPAY_WEB_RETURN_URL` |
| *(absent)* | `smilepay.app_return_url` | `SMILEPAY_APP_RETURN_URL` |

If none match, a JSON response is returned.

### Return URL Query Parameters

Regardless of which URL is used, the following query parameters are always appended:

| Parameter | Values | Description |
|---|---|---|
| `status` | `success`, `failed`, `cancelled`, `pending` | Payment outcome |
| `orderReference` | string | Your order reference |
| `message` | string | Human-readable status message (only on non-success) |

---

## Usage

### Standard Checkout

Standard Checkout redirects customers to a SmilePay hosted payment page.

```php
use Emmanuelsiziba\SmilePay\Facades\SmilePay;

$response = SmilePay::standardCheckout()->initiate([
    'amount' => 100.00,
    'itemName' => 'Premium Subscription',
    'itemDescription' => 'Monthly subscription',
    'email' => 'customer@example.com',
    'mobilePhoneNumber' => '0771234567',
    'firstName' => 'John',
    'lastName' => 'Doe',
]);

if ($response->isSuccessful()) {
    // Redirect customer to payment page
    return redirect($response->paymentUrl);
}
```

### Express Checkout

Express Checkout allows you to build custom payment UIs.

#### Ecocash

```php
use Emmanuelsiziba\SmilePay\Facades\SmilePay;

$response = SmilePay::expressCheckout()->ecocash([
    'amount' => 50.00,
    'itemName' => 'Product Purchase',
    'ecocashMobile' => '0771234567',
    'email' => 'customer@example.com',
]);

if ($response->isSuccessful()) {
    // Customer receives USSD push notification
    // Poll for payment status or wait for webhook
    echo "Payment initiated: {$response->transactionReference}";
}
```

#### Innbucks

```php
$response = SmilePay::expressCheckout()->innbucks([
    'amount' => 75.00,
    'itemName' => 'Service Payment',
    'email' => 'customer@example.com',
]);

if ($response->isSuccessful()) {
    // Get payment code
    $paymentCode = $response->innbucksPaymentCode;

    // Get deep link for mobile app
    $deepLink = $response->getInnbucksDeepLink();

    // Display to customer or create QR code
    echo "Payment Code: {$paymentCode}";
    echo "Deep Link: <a href='{$deepLink}'>Pay with InnBucks</a>";
}
```

#### Omari (Two-Step Process)

```php
// Step 1: Initiate payment (sends OTP)
$response = SmilePay::expressCheckout()->omari([
    'amount' => 100.00,
    'itemName' => 'Order #12345',
    'omariMobile' => '0771234567',
]);

$transactionRef = $response->transactionReference;

// Step 2: Confirm with OTP (customer enters OTP from SMS)
$confirmResponse = SmilePay::expressCheckout()->omariConfirm(
    $transactionRef,
    '123456', // OTP from customer
    '0771234567' // Same mobile number
);

if ($confirmResponse->isSuccessful()) {
    echo "Payment confirmed!";
}
```

#### SmileCash (Two-Step Process)

```php
// Step 1: Initiate payment
$response = SmilePay::expressCheckout()->smileCash([
    'amount' => 200.00,
    'itemName' => 'Shopping Cart',
    'smileCashMobile' => '0771234567',
]);

$transactionRef = $response->transactionReference;

// Step 2: Confirm with OTP
$confirmResponse = SmilePay::expressCheckout()->smileCashConfirm(
    $transactionRef,
    '000000', // OTP (use '000000' in sandbox)
    '0771234567'
);
```

#### Visa/Mastercard

```php
$response = SmilePay::expressCheckout()->card([
    'amount' => 150.00,
    'itemName' => 'Product Purchase',
    'pan' => '5123450000000008',
    'expMonth' => '01',
    'expYear' => '39',
    'securityCode' => '100',
    'email' => 'customer@example.com',
]);

if ($response->requires3DS()) {
    // Redirect to 3D Secure authentication
    $redirectHtml = $response->redirectHtml;
    echo $redirectHtml; // Renders 3DS form
}
```

### Transaction Management

#### Check Payment Status

```php
use Emmanuelsiziba\SmilePay\Facades\SmilePay;

$status = SmilePay::utility()->checkStatus('SP-ABC123-1234567890');

if ($status->isPaid()) {
    echo "Payment successful!";
    echo "Amount: {$status->amount}";
    echo "Payment Method: {$status->paymentOption}";
}

// Other status methods
$status->isPending();   // Returns true if pending
$status->isFailed();    // Returns true if failed
$status->isCanceled();  // Returns true if canceled
```

#### Cancel Payment

```php
$result = SmilePay::utility()->cancel('SP-ABC123-1234567890');

if ($result['success']) {
    echo "Payment canceled successfully";
}
```

#### Helper Methods

```php
// Quick boolean checks
$isPaid = SmilePay::utility()->isPaymentSuccessful('ORDER-REF');
$isPending = SmilePay::utility()->isPaymentPending('ORDER-REF');
$isFailed = SmilePay::utility()->isPaymentFailed('ORDER-REF');
```

### Webhook Handling

The package automatically registers a webhook route at `/smilepay/webhook` and sets it as the default `resultUrl` on every payment request — no manual configuration required.

To verify the route is registered:

```bash
php artisan route:list --name=smilepay
```

#### Listen to Payment Events

Create event listeners in `app/Listeners`:

```php
// app/Listeners/HandlePaymentReceived.php
namespace App\Listeners;

use Emmanuelsiziba\SmilePay\Events\PaymentReceived;

class HandlePaymentReceived
{
    public function handle(PaymentReceived $event)
    {
        $transaction = $event->transaction;

        // Update order status
        $order = Order::where('reference', $transaction->orderReference)->first();
        $order->update(['status' => 'paid']);

        // Send confirmation email
        Mail::to($order->email)->send(new PaymentConfirmation($order));
    }
}
```

Register the listener in `app/Providers/EventServiceProvider.php`:

```php
use Emmanuelsiziba\SmilePay\Events\PaymentReceived;
use Emmanuelsiziba\SmilePay\Events\PaymentFailed;
use Emmanuelsiziba\SmilePay\Events\PaymentCanceled;

protected $listen = [
    PaymentReceived::class => [
        HandlePaymentReceived::class,
    ],
    PaymentFailed::class => [
        HandlePaymentFailed::class,
    ],
    PaymentCanceled::class => [
        HandlePaymentCanceled::class,
    ],
];
```

## Events

The package dispatches the following events:

| Event | Description |
|-------|-------------|
| `PaymentReceived` | Fired when payment is successful (status: PAID) |
| `PaymentFailed` | Fired when payment fails (status: FAILED) |
| `PaymentCanceled` | Fired when payment is canceled (status: CANCELED) |

Each event contains a `$transaction` property of type `TransactionStatus`.

## Testing

### Sandbox Environment

Use the following test credentials in sandbox mode:

#### SmileCash
- **Mobile**: `0711111111`
- **OTP**: `000000`

#### Visa/Mastercard
- **Card Number**: `5123450000000008`
- **Expiry**: `01/39`
- **CVV**: `100`

#### Ecocash
- Use any valid Zimbabwe mobile number
- Approve the USSD prompt in sandbox

#### Innbucks
- Any payment code generated will work in sandbox

### Running Tests

```bash
composer test
```

## API Reference

### Payment Methods

| Method | Description |
|--------|-------------|
| `WALLETPLUS` | Combined wallet option |
| `ECOCASH` | Ecocash mobile money |
| `INNBUCKS` | Innbucks digital wallet |
| `CARD` | Visa/Mastercard |
| `OMARI` | Omari payment platform |
| `ONEMONEY` | OneMoney mobile money |

### Payment Statuses

| Status | Description |
|--------|-------------|
| `PAID` | Payment successful |
| `PENDING` | Payment pending confirmation |
| `FAILED` | Payment failed |
| `CANCELED` | Payment canceled |

## Advanced Usage

### Custom Order Reference

```php
$response = SmilePay::standardCheckout()->initiate([
    'orderReference' => 'CUSTOM-ORDER-' . time(),
    'amount' => 100.00,
    'itemName' => 'Product',
    // ... other fields
]);
```

### Using Dependency Injection

```php
use Emmanuelsiziba\SmilePay\Services\StandardCheckout;
use Emmanuelsiziba\SmilePay\Services\ExpressCheckout;

class PaymentController extends Controller
{
    public function __construct(
        protected StandardCheckout $standardCheckout,
        protected ExpressCheckout $expressCheckout
    ) {}

    public function processPayment()
    {
        $response = $this->expressCheckout->ecocash([...]);
    }
}
```

### Error Handling

The package provides comprehensive error handling with user-friendly error messages:

```php
use Emmanuelsiziba\SmilePay\Exceptions\SmilePayException;
use Emmanuelsiziba\SmilePay\Exceptions\PaymentException;

try {
    $response = SmilePay::expressCheckout()->ecocash([
        'amount' => 50.00,
        'itemName' => 'Product Purchase',
        'ecocashMobile' => '0771234567',
    ]);

    if ($response->isSuccessful()) {
        // Handle successful initiation
    }
} catch (PaymentException $e) {
    // Payment validation or business logic errors
    Log::error('Payment validation failed: ' . $e->getMessage());

    // Get additional context (API response details)
    $context = $e->getContext();

    return back()->withErrors([
        'payment' => $e->getMessage()
    ]);
} catch (SmilePayException $e) {
    // API communication errors (network, authentication, etc.)
    Log::error('SmilePay API error', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'context' => $e->getContext(),
    ]);

    return back()->withErrors([
        'payment' => 'Unable to process payment. Please try again later.'
    ]);
}
```

#### Common Error Scenarios

**404 Not Found Error**
```php
try {
    $response = SmilePay::expressCheckout()->ecocash([...]);
} catch (SmilePayException $e) {
    // User-friendly message:
    // "Endpoint not found: The payment method or endpoint '/payments/express-checkout/ecocash'
    // is not available. Please verify the API endpoint or contact support."

    if ($e->getCode() === 404) {
        // Check if you're using the correct environment
        // Verify the endpoint is available in your environment
        Log::warning('Payment endpoint not available', $e->getContext());
        return back()->with('warning', 'This payment method is currently unavailable.');
    }
}
```

**Authentication Error (401)**
```php
try {
    $response = SmilePay::expressCheckout()->innbucks([...]);
} catch (SmilePayException $e) {
    if ($e->getCode() === 401) {
        // "Authentication failed: Invalid API credentials.
        // Please check your SmilePay configuration."

        Log::critical('Invalid SmilePay credentials');
        return back()->withErrors(['payment' => 'Payment system configuration error.']);
    }
}
```

**Network/Connection Error**
```php
try {
    $response = SmilePay::standardCheckout()->initiate([...]);
} catch (SmilePayException $e) {
    // "Connection error: Unable to connect to SmilePay API.
    // Please check your internet connection and try again."

    Log::error('SmilePay connection failed', [
        'message' => $e->getMessage(),
        'context' => $e->getContext(),
    ]);

    return back()->withErrors([
        'payment' => 'Connection issue. Please check your internet and try again.'
    ]);
}
```

#### Error Context

All exceptions include a context array with debugging information:

```php
try {
    $response = SmilePay::expressCheckout()->ecocash([...]);
} catch (SmilePayException $e) {
    $context = $e->getContext();

    // Context includes:
    // - endpoint: The API endpoint that failed
    // - method: HTTP method (GET/POST)
    // - status_code: HTTP status code (if available)
    // - raw_response: Full API response (if available)

    Log::error('SmilePay error details', [
        'endpoint' => $context['endpoint'] ?? 'unknown',
        'status' => $context['status_code'] ?? 'N/A',
        'response' => $context['raw_response'] ?? 'No response',
    ]);
}
```

#### Best Practices

1. **Always catch exceptions** when initiating payments
2. **Log errors with context** for debugging
3. **Show user-friendly messages** to customers
4. **Handle specific error codes** differently (404, 401, 500, etc.)
5. **Enable logging** in `config/smilepay.php` for production debugging

## Troubleshooting

### 404 Not Found Error

If you encounter a 404 error when initiating payments:

```
Endpoint not found: The payment method or endpoint '/payments/express-checkout/ecocash'
is not available. Please verify the API endpoint or contact support.
```

**Possible causes:**
- **Wrong environment**: You may be using sandbox credentials with a production base URL (or vice versa)
- **Endpoint not available**: The specific payment method may not be enabled for your account
- **Incorrect base URL**: Check your `SMILEPAY_ENVIRONMENT` setting in `.env`
- **Outdated package version**: Versions prior to v0.1.5 had URL construction issues with Guzzle

**Solutions:**
1. **Update to the latest version** (recommended):
   ```bash
   composer update emmanuelsiziba/zb-laravel-smilepay
   ```
   This fixes a critical bug where leading slashes in endpoints caused Guzzle to treat them as absolute paths when the base URL has a trailing slash.

2. Verify your environment setting:
   ```env
   SMILEPAY_ENVIRONMENT=sandbox  # or 'production'
   ```

3. Ensure your API credentials match the environment

4. Check if the payment method is enabled in your SmilePay merchant dashboard

5. Enable logging to see the full request details:
   ```env
   SMILEPAY_LOGGING=true
   ```

### API Credentials Not Working

- Ensure you're using the correct environment (sandbox/production)
- Verify API keys are generated from the correct environment
- Check for any whitespace in your `.env` file
- Confirm credentials have the required permissions

### Webhook Not Receiving Callbacks

- The `resultUrl` is auto-set to `{APP_URL}/smilepay/webhook` — ensure your `APP_URL` in `.env` is publicly accessible (use [ngrok](https://ngrok.com) for local testing)
- Verify both routes are registered: `php artisan route:list --name=smilepay`
- Check webhook middleware in `config/smilepay.php`
- Check your server's firewall allows incoming POST requests

### Payment Status Not Updating

- Always poll the status API in addition to webhook callbacks
- Implement retry logic for failed webhook deliveries
- Use Laravel queues for webhook processing
- Check your application logs for webhook processing errors

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email security@example.com instead of using the issue tracker.

## Credits

- [Emmanuel Siziba](https://github.com/yourusername)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

- **Documentation**: [SmilePay Official Docs](https://docs.smilepay.id/)
- **Issues**: [GitHub Issues](https://github.com/emmanuelsiziba/zb-laravel-smilepay/issues)
- **Email**: support@example.com

---

Made with ❤️ for the Laravel community
