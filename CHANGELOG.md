# Changelog

All notable changes to `laravel-smilepay` will be documented in this file.

## v0.1.5 - 2025-01-19

### Fixed
- **Critical Bug Fix**: Removed leading slashes from all API endpoint paths to fix 404 errors
  - Fixed Guzzle URL construction issue where leading slashes in endpoints were treated as absolute paths when base URL has trailing slash
  - Updated all endpoints in ExpressCheckout, StandardCheckout, and PaymentUtility services
  - This resolves the "Endpoint not found" error for express checkout methods (ecocash, innbucks, omari, smilecash, card)
  - Affected endpoints:
    - `payments/express-checkout/ecocash`
    - `payments/express-checkout/innbucks`
    - `payments/express-checkout/omari`
    - `payments/express-checkout/omari/confirmation`
    - `payments/express-checkout/smilecash`
    - `payments/express-checkout/smilecash/confirmation`
    - `payments/express-checkout/mpgs`
    - `payments/initiate-transaction`
    - `payments/transaction/{ref}/status/check`
    - `payments/cancel/{ref}`

### Changed
- Updated README troubleshooting section with information about this fix

## v1.0.0 - 2025-01-XX

### Added
- Initial release
- Standard Checkout integration
- Express Checkout for Ecocash
- Express Checkout for Innbucks
- Express Checkout for Omari
- Express Checkout for SmileCash
- Express Checkout for Visa/Mastercard
- Webhook handler for payment callbacks
- Laravel events for payment notifications
- Transaction status checking
- Payment cancellation
- Comprehensive logging
- Sandbox and production environment support
- Full documentation and examples
