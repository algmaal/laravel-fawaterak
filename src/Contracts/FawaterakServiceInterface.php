<?php

namespace Algmaal\LaravelFawaterak\Contracts;

interface FawaterakServiceInterface
{
    /**
     * Get available payment methods.
     *
     * @return array
     */
    public function getPaymentMethods(): array;

    /**
     * Initiate a payment.
     *
     * @param array $paymentData
     * @return array
     */
    public function initiatePayment(array $paymentData): array;

    /**
     * Get transaction status.
     *
     * @param string $invoiceKey
     * @return array
     */
    public function getTransactionStatus(string $invoiceKey): array;

    /**
     * Verify webhook signature.
     *
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * Make HTTP request to Fawaterak API.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    public function makeRequest(string $method, string $endpoint, array $data = []): array;
}
