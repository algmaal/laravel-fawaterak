<?php

namespace Algmaal\LaravelFawaterak\Tests\Unit;

use Algmaal\LaravelFawaterak\Contracts\FawaterakServiceInterface;
use Algmaal\LaravelFawaterak\Contracts\PaymentServiceInterface;
use Algmaal\LaravelFawaterak\Exceptions\InvalidPaymentDataException;
use Algmaal\LaravelFawaterak\Services\PaymentService;
use Algmaal\LaravelFawaterak\Tests\TestCase;
use Mockery;

class PaymentServiceTest extends TestCase
{
    protected PaymentService $paymentService;
    protected $mockFawaterakService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockFawaterakService = Mockery::mock(FawaterakServiceInterface::class);
        $this->paymentService = new PaymentService($this->mockFawaterakService);
    }

    public function test_service_implements_interface()
    {
        $this->assertInstanceOf(PaymentServiceInterface::class, $this->paymentService);
    }

    public function test_create_payment_success()
    {
        $customerData = $this->getValidCustomerData();
        $cartItems = $this->getValidCartItems();
        $total = 200.0;
        $paymentMethodId = 2;

        $expectedPaymentData = [
            'payment_method_id' => $paymentMethodId,
            'cartTotal' => '200',
            'currency' => 'EGP',
            'customer' => $customerData,
            'cartItems' => $cartItems,
            'redirectionUrls' => [
                'successUrl' => url('/payment/success'),
                'failUrl' => url('/payment/failed'),
                'pendingUrl' => url('/payment/pending'),
            ],
        ];

        $expectedResponse = $this->getValidInitiatePaymentResponse();

        $this->mockFawaterakService
            ->shouldReceive('initiatePayment')
            ->once()
            ->with($expectedPaymentData)
            ->andReturn($expectedResponse);

        $result = $this->paymentService->createPayment(
            $customerData,
            $cartItems,
            $total,
            $paymentMethodId
        );

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_create_payment_with_options()
    {
        $customerData = $this->getValidCustomerData();
        $cartItems = $this->getValidCartItems();
        $total = 200.0;
        $paymentMethodId = 2;
        $options = [
            'currency' => 'USD',
            'invoice_number' => 'INV-001',
            'redirection_urls' => [
                'successUrl' => 'https://example.com/success',
                'failUrl' => 'https://example.com/fail',
                'pendingUrl' => 'https://example.com/pending',
            ],
        ];

        $expectedPaymentData = [
            'payment_method_id' => $paymentMethodId,
            'cartTotal' => '200',
            'currency' => 'USD',
            'customer' => $customerData,
            'cartItems' => $cartItems,
            'invoice_number' => 'INV-001',
            'redirectionUrls' => $options['redirection_urls'],
        ];

        $expectedResponse = $this->getValidInitiatePaymentResponse();

        $this->mockFawaterakService
            ->shouldReceive('initiatePayment')
            ->once()
            ->with($expectedPaymentData)
            ->andReturn($expectedResponse);

        $result = $this->paymentService->createPayment(
            $customerData,
            $cartItems,
            $total,
            $paymentMethodId,
            $options
        );

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_create_payment_invalid_customer_data()
    {
        $this->expectException(InvalidPaymentDataException::class);
        $this->expectExceptionMessage("Customer field 'first_name' is required");

        $invalidCustomerData = [
            'last_name' => 'أحمد',
            'email' => 'mohamed@example.com',
        ];

        $this->paymentService->createPayment(
            $invalidCustomerData,
            $this->getValidCartItems(),
            200.0,
            2
        );
    }

    public function test_create_payment_invalid_email()
    {
        $this->expectException(InvalidPaymentDataException::class);
        $this->expectExceptionMessage('Invalid email address');

        $invalidCustomerData = [
            'first_name' => 'محمد',
            'last_name' => 'أحمد',
            'email' => 'invalid-email',
        ];

        $this->paymentService->createPayment(
            $invalidCustomerData,
            $this->getValidCartItems(),
            200.0,
            2
        );
    }

    public function test_create_payment_empty_cart_items()
    {
        $this->expectException(InvalidPaymentDataException::class);
        $this->expectExceptionMessage('Cart items cannot be empty');

        $this->paymentService->createPayment(
            $this->getValidCustomerData(),
            [],
            200.0,
            2
        );
    }

    public function test_create_payment_invalid_cart_item()
    {
        $this->expectException(InvalidPaymentDataException::class);
        $this->expectExceptionMessage('Cart item 0: name is required');

        $invalidCartItems = [
            [
                'price' => '100',
                'quantity' => '1',
            ],
        ];

        $this->paymentService->createPayment(
            $this->getValidCustomerData(),
            $invalidCartItems,
            200.0,
            2
        );
    }

    public function test_get_payment_success()
    {
        $invoiceKey = 'hyU2vcy3USvT5Tg';
        $expectedResponse = $this->getValidTransactionStatusResponse();

        $this->mockFawaterakService
            ->shouldReceive('getTransactionStatus')
            ->once()
            ->with($invoiceKey)
            ->andReturn($expectedResponse);

        $result = $this->paymentService->getPayment($invoiceKey);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_is_payment_successful_true()
    {
        $invoiceKey = 'hyU2vcy3USvT5Tg';
        $response = $this->getValidTransactionStatusResponse();

        $this->mockFawaterakService
            ->shouldReceive('getTransactionStatus')
            ->once()
            ->with($invoiceKey)
            ->andReturn($response);

        $result = $this->paymentService->isPaymentSuccessful($invoiceKey);

        $this->assertTrue($result);
    }

    public function test_is_payment_successful_false()
    {
        $invoiceKey = 'hyU2vcy3USvT5Tg';
        $response = [
            'status' => 'success',
            'data' => [
                'invoice_status' => 'pending',
            ],
        ];

        $this->mockFawaterakService
            ->shouldReceive('getTransactionStatus')
            ->once()
            ->with($invoiceKey)
            ->andReturn($response);

        $result = $this->paymentService->isPaymentSuccessful($invoiceKey);

        $this->assertFalse($result);
    }

    public function test_get_payment_status()
    {
        $invoiceKey = 'hyU2vcy3USvT5Tg';
        $response = $this->getValidTransactionStatusResponse();

        $this->mockFawaterakService
            ->shouldReceive('getTransactionStatus')
            ->once()
            ->with($invoiceKey)
            ->andReturn($response);

        $result = $this->paymentService->getPaymentStatus($invoiceKey);

        $this->assertEquals('paid', $result);
    }

    public function test_process_webhook_success()
    {
        $webhookData = [
            'invoice_key' => 'hyU2vcy3USvT5Tg',
            'status' => 'paid',
        ];

        $paymentResponse = $this->getValidTransactionStatusResponse();

        $this->mockFawaterakService
            ->shouldReceive('getTransactionStatus')
            ->once()
            ->with('hyU2vcy3USvT5Tg')
            ->andReturn($paymentResponse);

        $result = $this->paymentService->processWebhook($webhookData);

        $this->assertEquals('hyU2vcy3USvT5Tg', $result['invoice_key']);
        $this->assertEquals('paid', $result['status']);
        $this->assertEquals($paymentResponse['data'], $result['payment_data']);
        $this->assertEquals($webhookData, $result['webhook_data']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
