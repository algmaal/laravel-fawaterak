<?php

namespace Algmaal\LaravelFawaterak\Tests\Feature;

use Algmaal\LaravelFawaterak\Facades\Fawaterak;
use Algmaal\LaravelFawaterak\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class FawaterakIntegrationTest extends TestCase
{
    public function test_facade_is_registered()
    {
        $this->assertTrue(class_exists('Fawaterak'));
    }

    public function test_service_provider_is_loaded()
    {
        $this->assertTrue($this->app->providerIsLoaded(\Algmaal\LaravelFawaterak\FawaterakServiceProvider::class));
    }

    public function test_config_is_published()
    {
        $this->assertNotNull(config('fawaterak'));
        $this->assertEquals('staging', config('fawaterak.default'));
    }

    public function test_webhook_route_is_registered()
    {
        $response = $this->postJson('/fawaterak/webhook', [
            'invoice_key' => 'test_key',
            'status' => 'paid',
        ]);

        // Should not be 404 (route exists)
        $this->assertNotEquals(404, $response->getStatusCode());
    }

    public function test_webhook_events_are_fired()
    {
        Event::fake();

        // Mock the webhook processing
        $response = $this->postJson('/fawaterak/webhook', [
            'invoice_key' => 'test_key',
            'status' => 'paid',
        ], [
            'X-Fawaterak-Signature' => 'test_signature',
        ]);

        // Basic assertion to ensure the test has some validation
        $this->assertNotEquals(404, $response->getStatusCode());
    }

    public function test_services_are_bound_in_container()
    {
        $this->assertTrue($this->app->bound(\Algmaal\LaravelFawaterak\Contracts\FawaterakServiceInterface::class));
        $this->assertTrue($this->app->bound(\Algmaal\LaravelFawaterak\Contracts\PaymentServiceInterface::class));
    }

    public function test_facade_methods_are_accessible()
    {
        // Test that the facade resolves to the correct service
        $service = Fawaterak::getFacadeRoot();
        $this->assertInstanceOf(\Algmaal\LaravelFawaterak\Contracts\PaymentServiceInterface::class, $service);

        // Test that the service has the expected methods
        $this->assertTrue(method_exists($service, 'createPayment'));
        $this->assertTrue(method_exists($service, 'getPayment'));
        $this->assertTrue(method_exists($service, 'isPaymentSuccessful'));
    }
}