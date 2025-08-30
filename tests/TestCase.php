<?php

namespace Algmaal\LaravelFawaterak\Tests;

use Algmaal\LaravelFawaterak\FawaterakServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpConfig();
    }

    protected function getPackageProviders($app): array
    {
        return [
            FawaterakServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Fawaterak' => \Algmaal\LaravelFawaterak\Facades\Fawaterak::class,
        ];
    }

    protected function setUpConfig(): void
    {
        config([
            'fawaterak.default' => 'staging',
            'fawaterak.environments.staging' => [
                'api_key' => 'test_api_key',
                'base_url' => 'https://staging.fawaterk.com',
                'webhook_secret' => 'test_webhook_secret',
            ],
            'fawaterak.environments.production' => [
                'api_key' => 'prod_api_key',
                'base_url' => 'https://app.fawaterak.com',
                'webhook_secret' => 'prod_webhook_secret',
            ],
            'fawaterak.endpoints' => [
                'payment_methods' => '/api/v2/getPaymentmethods',
                'initiate_payment' => '/api/v2/invoiceInitPay',
                'transaction_status' => '/api/v2/getInvoiceData',
            ],
            'fawaterak.default_currency' => 'EGP',
            'fawaterak.supported_currencies' => ['EGP', 'USD', 'SAR', 'AED', 'KWD', 'QAR', 'BHD'],
            'fawaterak.http' => [
                'timeout' => 30,
                'connect_timeout' => 10,
                'verify' => true,
            ],
            'fawaterak.logging' => [
                'enabled' => false,
                'channel' => 'default',
                'level' => 'info',
                'log_requests' => true,
                'log_responses' => true,
            ],
            'fawaterak.webhook' => [
                'verify_signature' => true,
                'tolerance' => 300,
                'middleware' => ['api'],
            ],
            'fawaterak.cache' => [
                'enabled' => false,
                'ttl' => 3600,
                'prefix' => 'fawaterak',
            ],
            'fawaterak.default_urls' => [
                'success_url' => '/payment/success',
                'fail_url' => '/payment/failed',
                'pending_url' => '/payment/pending',
            ],
        ]);
    }

    protected function mockSuccessfulApiResponse(array $data = []): array
    {
        return array_merge([
            'status' => 'success',
            'data' => $data,
        ], $data);
    }

    protected function mockFailedApiResponse(string $message = 'Error occurred'): array
    {
        return [
            'status' => 'error',
            'message' => $message,
        ];
    }

    protected function getValidCustomerData(): array
    {
        return [
            'first_name' => 'محمد',
            'last_name' => 'أحمد',
            'email' => 'mohamed@example.com',
            'phone' => '01234567890',
            'address' => 'القاهرة، مصر',
        ];
    }

    protected function getValidCartItems(): array
    {
        return [
            [
                'name' => 'منتج تجريبي 1',
                'price' => '100',
                'quantity' => '1',
            ],
            [
                'name' => 'منتج تجريبي 2',
                'price' => '50',
                'quantity' => '2',
            ],
        ];
    }

    protected function getValidPaymentMethodsResponse(): array
    {
        return [
            'status' => 'success',
            'data' => [
                [
                    'paymentId' => 2,
                    'name_en' => 'Visa-Mastercard',
                    'name_ar' => 'فيزا -ماستر كارد',
                    'redirect' => 'true',
                    'logo' => 'https://app.fawaterak.xyz/clients/payment_options/mastercard-visa.png',
                ],
                [
                    'paymentId' => 3,
                    'name_en' => 'Fawry',
                    'name_ar' => 'فوري',
                    'redirect' => 'false',
                    'logo' => 'https://app.fawaterak.xyz/clients/payment_options/fawry.png',
                ],
            ],
        ];
    }

    protected function getValidInitiatePaymentResponse(): array
    {
        return [
            'status' => 'success',
            'data' => [
                'invoice_id' => 1000428,
                'invoice_key' => 'hyU2vcy3USvT5Tg',
                'payment_data' => [
                    'redirectTo' => 'https://staging.fawaterk.com/link/I0PAH',
                ],
            ],
        ];
    }

    protected function getValidTransactionStatusResponse(): array
    {
        return [
            'status' => 'success',
            'data' => [
                'invoice_id' => 1000428,
                'invoice_key' => 'hyU2vcy3USvT5Tg',
                'invoice_status' => 'paid',
                'invoice_reference' => 'INV-001',
                'cart_total' => '200.00',
                'currency' => 'EGP',
                'customer' => $this->getValidCustomerData(),
                'cart_items' => $this->getValidCartItems(),
            ],
        ];
    }
}
