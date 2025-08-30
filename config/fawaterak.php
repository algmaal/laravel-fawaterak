<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Environment
    |--------------------------------------------------------------------------
    |
    | This option controls the default environment that will be used by the
    | Fawaterak package. You may set this to any of the environments
    | defined in the "environments" array below.
    |
    */
    'default' => env('FAWATERAK_ENVIRONMENT', 'staging'),

    /*
    |--------------------------------------------------------------------------
    | Environments Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each environment. The package
    | supports both staging and production environments with different
    | API endpoints and credentials.
    |
    */
    'environments' => [
        'staging' => [
            'api_key' => env('FAWATERAK_STAGING_API_KEY'),
            'base_url' => env('FAWATERAK_STAGING_BASE_URL', 'https://staging.fawaterk.com'),
            'webhook_secret' => env('FAWATERAK_STAGING_WEBHOOK_SECRET'),
        ],
        'production' => [
            'api_key' => env('FAWATERAK_PRODUCTION_API_KEY'),
            'base_url' => env('FAWATERAK_PRODUCTION_BASE_URL', 'https://app.fawaterak.com'),
            'webhook_secret' => env('FAWATERAK_PRODUCTION_WEBHOOK_SECRET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    |
    | These are the API endpoints used by the Fawaterak package.
    | You should not need to modify these unless Fawaterak changes
    | their API structure.
    |
    */
    'endpoints' => [
        'payment_methods' => '/api/v2/getPaymentmethods',
        'initiate_payment' => '/api/v2/invoiceInitPay',
        'transaction_status' => '/api/v2/getInvoiceData',
        'webhook' => '/api/v2/webhook',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency to use for payments. Supported currencies:
    | EGP, USD, SAR, AED, KWD, QAR, BHD
    |
    */
    'default_currency' => env('FAWATERAK_DEFAULT_CURRENCY', 'EGP'),

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | List of currencies supported by Fawaterak
    |
    */
    'supported_currencies' => [
        'EGP', 'USD', 'SAR', 'AED', 'KWD', 'QAR', 'BHD'
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the HTTP client used to communicate with
    | the Fawaterak API.
    |
    */
    'http' => [
        'timeout' => env('FAWATERAK_HTTP_TIMEOUT', 30),
        'connect_timeout' => env('FAWATERAK_HTTP_CONNECT_TIMEOUT', 10),
        'verify' => env('FAWATERAK_HTTP_VERIFY_SSL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how the package should log API requests and responses.
    |
    */
    'logging' => [
        'enabled' => env('FAWATERAK_LOGGING_ENABLED', true),
        'channel' => env('FAWATERAK_LOG_CHANNEL', 'default'),
        'level' => env('FAWATERAK_LOG_LEVEL', 'info'),
        'log_requests' => env('FAWATERAK_LOG_REQUESTS', true),
        'log_responses' => env('FAWATERAK_LOG_RESPONSES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for handling Fawaterak webhooks
    |
    */
    'webhook' => [
        'verify_signature' => env('FAWATERAK_WEBHOOK_VERIFY_SIGNATURE', true),
        'tolerance' => env('FAWATERAK_WEBHOOK_TOLERANCE', 300), // 5 minutes
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching payment methods and other data
    |
    */
    'cache' => [
        'enabled' => env('FAWATERAK_CACHE_ENABLED', true),
        'ttl' => env('FAWATERAK_CACHE_TTL', 3600), // 1 hour
        'prefix' => env('FAWATERAK_CACHE_PREFIX', 'fawaterak'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Redirection URLs
    |--------------------------------------------------------------------------
    |
    | Default URLs for payment success, failure, and pending states
    |
    */
    'default_urls' => [
        'success_url' => env('FAWATERAK_SUCCESS_URL', '/payment/success'),
        'fail_url' => env('FAWATERAK_FAIL_URL', '/payment/failed'),
        'pending_url' => env('FAWATERAK_PENDING_URL', '/payment/pending'),
    ],
];
