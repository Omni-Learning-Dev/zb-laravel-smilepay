# Laravel SmilePay Package - Structure Documentation

## Package Overview

This is a comprehensive Laravel package for integrating SmilePay Payment Gateway (ZB Bank Zimbabwe) into Laravel applications.

## Directory Structure

```
smilepay/
├── config/
│   └── smilepay.php                          # Configuration file
├── routes/
│   └── web.php                               # Webhook route registration
├── src/
│   ├── Client/
│   │   └── SmilePayClient.php                # HTTP client for API communication
│   ├── DataObjects/
│   │   ├── PaymentResponse.php               # Payment response DTO
│   │   └── TransactionStatus.php             # Transaction status DTO
│   ├── Events/
│   │   ├── PaymentReceived.php               # Event: Payment successful
│   │   ├── PaymentFailed.php                 # Event: Payment failed
│   │   └── PaymentCanceled.php               # Event: Payment canceled
│   ├── Exceptions/
│   │   ├── SmilePayException.php             # Base exception
│   │   └── PaymentException.php              # Payment-specific exception
│   ├── Facades/
│   │   └── SmilePay.php                      # Laravel facade
│   ├── Http/
│   │   └── Controllers/
│   │       └── WebhookController.php         # Webhook handler
│   ├── Services/
│   │   ├── StandardCheckout.php              # Standard checkout implementation
│   │   ├── ExpressCheckout.php               # Express checkout implementation
│   │   └── PaymentUtility.php                # Utility methods
│   ├── SmilePay.php                          # Main service class
│   └── SmilePayServiceProvider.php           # Laravel service provider
├── .env.example                              # Environment variables template
├── .gitignore                                # Git ignore rules
├── CHANGELOG.md                              # Version history
├── composer.json                             # Package dependencies
├── EXAMPLES.md                               # Detailed usage examples
├── LICENSE.md                                # MIT License
└── README.md                                 # Main documentation
```

## Core Components

### 1. SmilePayClient (src/Client/SmilePayClient.php)

**Purpose**: Handles all HTTP communication with SmilePay API

**Key Features**:
- Automatic authentication header injection (`x-api-key`, `x-api-secret`)
- Environment-based URL switching (sandbox/production)
- Built-in request/response logging
- Error handling with detailed context
- Configurable timeouts

**Methods**:
- `post($endpoint, $data)` - Make POST requests
- `get($endpoint, $params)` - Make GET requests

### 2. Services

#### StandardCheckout (src/Services/StandardCheckout.php)

**Purpose**: Hosted payment page integration

**Methods**:
- `initiate(array $data): PaymentResponse` - Initiate standard checkout

**Returns**: Payment URL for customer redirect

#### ExpressCheckout (src/Services/ExpressCheckout.php)

**Purpose**: Direct API integration for custom UIs

**Methods**:
- `innbucks(array $data): PaymentResponse`
- `ecocash(array $data): PaymentResponse`
- `omari(array $data): PaymentResponse`
- `omariConfirm(string $ref, string $otp, string $mobile): PaymentResponse`
- `smileCash(array $data): PaymentResponse`
- `smileCashConfirm(string $ref, string $otp, string $mobile): PaymentResponse`
- `card(array $data): PaymentResponse`
- `getInnbucksDeepLink(string $code): string`

#### PaymentUtility (src/Services/PaymentUtility.php)

**Purpose**: Transaction management utilities

**Methods**:
- `checkStatus(string $orderRef): TransactionStatus`
- `cancel(string $orderRef): array`
- `isPaymentSuccessful(string $orderRef): bool`
- `isPaymentPending(string $orderRef): bool`
- `isPaymentFailed(string $orderRef): bool`

### 3. Data Objects

#### PaymentResponse (src/DataObjects/PaymentResponse.php)

**Properties**:
- `responseMessage: string`
- `responseCode: string`
- `paymentUrl: ?string`
- `transactionReference: ?string`
- `status: ?string`
- `innbucksPaymentCode: ?string`
- `redirectHtml: ?string`
- `customizedHtml: ?array`

**Methods**:
- `isSuccessful(): bool`
- `getInnbucksDeepLink(): ?string`
- `requires3DS(): bool`
- `get3DSUrl(): ?string`

#### TransactionStatus (src/DataObjects/TransactionStatus.php)

**Properties**:
- `merchantId: string`
- `orderReference: string`
- `amount: float`
- `currency: string`
- `status: string`
- `paymentOption: string`
- `clientFee: ?float`
- `merchantFee: ?float`

**Methods**:
- `isPaid(): bool`
- `isPending(): bool`
- `isFailed(): bool`
- `isCanceled(): bool`
- `getTotalFees(): float`
- `getNetAmount(): float`

### 4. Events System

Three Laravel events for payment lifecycle:

1. **PaymentReceived** - Status: PAID
2. **PaymentFailed** - Status: FAILED
3. **PaymentCanceled** - Status: CANCELED

Each event contains a `TransactionStatus` object with complete payment details.

### 5. Webhook Handler

**Route**: `POST /smilepay/webhook`

**Features**:
- Automatic payload validation
- Event dispatching based on status
- Comprehensive logging
- Error handling with proper HTTP responses

### 6. Configuration (config/smilepay.php)

**Key Settings**:
- Environment (sandbox/production)
- API credentials
- Base URLs
- Default currency
- Webhook settings
- Timeout configuration
- Logging preferences

## API Endpoints Covered

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/payments/initiate-transaction` | POST | Standard checkout |
| `/payments/express-checkout/innbucks` | POST | Innbucks payment |
| `/payments/express-checkout/ecocash` | POST | Ecocash payment |
| `/payments/express-checkout/omari` | POST | Omari payment (Leg 1) |
| `/payments/express-checkout/omari/confirmation` | POST | Omari confirmation (Leg 2) |
| `/payments/express-checkout/smilecash` | POST | SmileCash payment |
| `/payments/express-checkout/smilecash/confirmation` | POST | SmileCash confirmation |
| `/payments/express-checkout/mpgs` | POST | Card payment (3DS) |
| `/payments/transaction/{ref}/status/check` | GET | Check status |
| `/payments/cancel/{ref}` | POST | Cancel payment |

## Payment Methods Supported

1. **WALLETPLUS** - Combined wallet
2. **ECOCASH** - Mobile money (USSD push)
3. **INNBUCKS** - Digital wallet (payment code)
4. **CARD** - Visa/Mastercard (3DS)
5. **OMARI** - Payment platform (OTP)
6. **SMILECASH** - Digital wallet (OTP)
7. **ONEMONEY** - Mobile money

## Payment Statuses

- **PAID** - Payment successful
- **PENDING** - Awaiting confirmation
- **FAILED** - Payment failed
- **CANCELED** - Payment canceled

## Environment Variables Required

```env
SMILEPAY_ENVIRONMENT=sandbox
SMILEPAY_API_KEY=your_key
SMILEPAY_API_SECRET=your_secret
SMILEPAY_DEFAULT_CURRENCY=840
SMILEPAY_RETURN_URL=https://yourdomain.com/payment/return
SMILEPAY_RESULT_URL=https://yourdomain.com/smilepay/webhook
SMILEPAY_CANCEL_URL=https://yourdomain.com/payment/cancel
SMILEPAY_FAILURE_URL=https://yourdomain.com/payment/failed
```

## Usage Examples

### Quick Start

```php
use Emmanuelsiziba\SmilePay\Facades\SmilePay;

// Standard checkout
$response = SmilePay::standardCheckout()->initiate([
    'amount' => 100.00,
    'itemName' => 'Product',
    'email' => 'customer@example.com',
]);

return redirect($response->paymentUrl);

// Express checkout (Ecocash)
$response = SmilePay::expressCheckout()->ecocash([
    'amount' => 50.00,
    'itemName' => 'Service',
    'ecocashMobile' => '0771234567',
]);

// Check status
$status = SmilePay::utility()->checkStatus('ORDER-REF');

if ($status->isPaid()) {
    // Process order
}
```

## Testing

### Sandbox Credentials

**SmileCash**:
- Mobile: `0711111111`
- OTP: `000000`

**Visa/Mastercard**:
- Card: `5123450000000008`
- Expiry: `01/39`
- CVV: `100`

## Dependencies

- PHP 8.0+
- Laravel 9.x/10.x/11.x
- Guzzle HTTP 7.x

## Installation Steps

1. `composer require emmanuelsiziba/zb-laravel-smilepay`
2. `php artisan vendor:publish --tag=smilepay-config`
3. Configure `.env` with API credentials
4. Listen to payment events in EventServiceProvider
5. Start processing payments

## Security Considerations

- API credentials stored in environment variables
- All API calls over HTTPS
- Webhook signature validation (implement if provided by SmilePay)
- Request/response logging for audit trails
- Exception handling to prevent credential leakage

## Extensibility

The package is designed for easy extension:

1. Add custom payment methods by extending `ExpressCheckout`
2. Implement custom event listeners for business logic
3. Override configuration values per-request
4. Add middleware to webhook routes
5. Customize logging channels

## Contributing

1. Fork the repository
2. Create feature branch
3. Write tests
4. Submit pull request

## Support

- GitHub Issues
- Email support
- Documentation website

---

**Version**: 1.0.0
**License**: MIT
**Author**: Emmanuel Siziba
