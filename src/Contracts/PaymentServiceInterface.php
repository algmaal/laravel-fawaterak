<?php

namespace Algmaal\LaravelFawaterak\Contracts;

interface PaymentServiceInterface
{
    /**
     * Create a new payment.
     *
     * @param array $customerData
     * @param array $cartItems
     * @param float $total
     * @param int $paymentMethodId
     * @param array $options
     * @return array
     */
    public function createPayment(
        array $customerData,
        array $cartItems,
        float $total,
        int $paymentMethodId,
        array $options = []
    ): array;

    /**
     * Get payment by invoice key.
     *
     * @param string $invoiceKey
     * @return array
     */
    public function getPayment(string $invoiceKey): array;

    /**
     * Check if payment is successful.
     *
     * @param string $invoiceKey
     * @return bool
     */
    public function isPaymentSuccessful(string $invoiceKey): bool;

    /**
     * Get payment status.
     *
     * @param string $invoiceKey
     * @return string
     */
    public function getPaymentStatus(string $invoiceKey): string;

    /**
     * Process webhook data.
     *
     * @param array $webhookData
     * @return array
     */
    public function processWebhook(array $webhookData): array;
}
