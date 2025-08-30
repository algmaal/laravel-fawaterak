# Changelog

All notable changes to `laravel-fawaterak` will be documented in this file.

## [1.0.3] - 2024-08-30

### Added

- GitHub webhook integration for Packagist auto-updates
- Repository description and proper GitHub setup

## [1.0.0] - 2024-08-30

### Added

- Initial release
- Full integration with Fawaterak API
- Support for all payment methods (Visa/Mastercard, Fawry, Meeza, Aman, Basta)
- Webhook handling with automatic signature verification
- Multi-environment support (staging/production)
- Comprehensive error handling and validation
- Caching support for payment methods
- Logging for all API interactions
- Laravel 10+ and 11+ compatibility
- Facade for easy usage
- Complete test suite
- Arabic and English documentation

### Features

- `FawaterakService` for direct API communication
- `PaymentService` for high-level payment operations
- `WebhookController` for handling Fawaterak notifications
- Custom exceptions for better error handling
- Configuration file with extensive options
- Event system for webhook processing
- Service provider with auto-discovery
- Comprehensive validation for all inputs

### Supported Payment Methods

- Visa/Mastercard (redirect-based)
- Fawry (code-based)
- Meeza Mobile Wallet (QR code-based)
- Aman (code-based)
- Basta (code-based)

### API Endpoints

- `GET /api/v2/getPaymentmethods` - Retrieve available payment methods
- `POST /api/v2/invoiceInitPay` - Initiate payment
- `POST /api/v2/getInvoiceData` - Get transaction status
- `POST /fawaterak/webhook` - Handle webhooks

### Configuration Options

- Multi-environment support
- Customizable HTTP client settings
- Flexible logging configuration
- Cache settings for performance
- Webhook security settings
- Default redirection URLs
