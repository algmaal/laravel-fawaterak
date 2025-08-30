<?php

namespace Algmaal\LaravelFawaterak\Tests\Unit;

use Algmaal\LaravelFawaterak\Contracts\FawaterakServiceInterface;
use Algmaal\LaravelFawaterak\Exceptions\FawaterakException;
use Algmaal\LaravelFawaterak\Exceptions\InvalidConfigurationException;
use Algmaal\LaravelFawaterak\Services\FawaterakService;
use Algmaal\LaravelFawaterak\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;

class FawaterakServiceTest extends TestCase
{
    protected FawaterakService $service;
    protected MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handlerStack]);
        
        $this->service = new FawaterakService(config('fawaterak'));
        
        // Use reflection to set the HTTP client
        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);
    }

    public function test_service_implements_interface()
    {
        $this->assertInstanceOf(FawaterakServiceInterface::class, $this->service);
    }

    public function test_invalid_configuration_throws_exception()
    {
        $this->expectException(InvalidConfigurationException::class);
        
        new FawaterakService([
            'default' => 'invalid_env',
            'environments' => [],
        ]);
    }

    public function test_missing_api_key_throws_exception()
    {
        $this->expectException(InvalidConfigurationException::class);
        
        new FawaterakService([
            'default' => 'staging',
            'environments' => [
                'staging' => [
                    'base_url' => 'https://staging.fawaterk.com',
                ],
            ],
        ]);
    }

    public function test_get_payment_methods_success()
    {
        $expectedResponse = $this->getValidPaymentMethodsResponse();
        
        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->service->getPaymentMethods();

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_payment_methods_api_error()
    {
        $this->expectException(FawaterakException::class);
        
        $this->mockHandler->append(
            new Response(400, [], json_encode([
                'status' => 'error',
                'message' => 'Invalid request',
            ]))
        );

        $this->service->getPaymentMethods();
    }

    public function test_initiate_payment_success()
    {
        $expectedResponse = $this->getValidInitiatePaymentResponse();
        
        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $paymentData = [
            'payment_method_id' => 2,
            'cartTotal' => '200',
            'currency' => 'EGP',
            'customer' => $this->getValidCustomerData(),
            'cartItems' => $this->getValidCartItems(),
        ];

        $result = $this->service->initiatePayment($paymentData);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_initiate_payment_validation_error()
    {
        $this->expectException(FawaterakException::class);
        $this->expectExceptionMessage("Required field 'payment_method_id' is missing");

        $paymentData = [
            'cartTotal' => '200',
            'currency' => 'EGP',
        ];

        $this->service->initiatePayment($paymentData);
    }

    public function test_initiate_payment_unsupported_currency()
    {
        $this->expectException(FawaterakException::class);
        $this->expectExceptionMessage('Unsupported currency: XXX');

        $paymentData = [
            'payment_method_id' => 2,
            'cartTotal' => '200',
            'currency' => 'XXX',
            'customer' => $this->getValidCustomerData(),
            'cartItems' => $this->getValidCartItems(),
        ];

        $this->service->initiatePayment($paymentData);
    }

    public function test_get_transaction_status_success()
    {
        $expectedResponse = $this->getValidTransactionStatusResponse();
        
        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->service->getTransactionStatus('hyU2vcy3USvT5Tg');

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_verify_webhook_signature_valid()
    {
        $payload = json_encode(['test' => 'data']);
        $secret = 'test_webhook_secret';
        $signature = hash_hmac('sha256', $payload, $secret);

        $result = $this->service->verifyWebhookSignature($payload, $signature);

        $this->assertTrue($result);
    }

    public function test_verify_webhook_signature_invalid()
    {
        $payload = json_encode(['test' => 'data']);
        $signature = 'invalid_signature';

        $result = $this->service->verifyWebhookSignature($payload, $signature);

        $this->assertFalse($result);
    }

    public function test_http_request_exception()
    {
        $this->expectException(FawaterakException::class);
        
        $this->mockHandler->append(
            new RequestException(
                'Connection timeout',
                new Request('GET', 'test')
            )
        );

        $this->service->getPaymentMethods();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
