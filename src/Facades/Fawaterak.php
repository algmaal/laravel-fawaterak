<?php

namespace Algmaal\LaravelFawaterak\Facades;

use Algmaal\LaravelFawaterak\Contracts\PaymentServiceInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array getPaymentMethods()
 * @method static array createPayment(array $customerData, array $cartItems, float $total, int $paymentMethodId, array $options = [])
 * @method static array getPayment(string $invoiceKey)
 * @method static bool isPaymentSuccessful(string $invoiceKey)
 * @method static string getPaymentStatus(string $invoiceKey)
 * @method static array processWebhook(array $webhookData)
 *
 * @see \Algmaal\LaravelFawaterak\Services\PaymentService
 */
class Fawaterak extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return PaymentServiceInterface::class;
    }
}
