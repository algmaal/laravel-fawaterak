<?php

namespace Algmaal\LaravelFawaterak\Services;

use Algmaal\LaravelFawaterak\Contracts\FawaterakServiceInterface;
use Algmaal\LaravelFawaterak\Contracts\PaymentServiceInterface;
use Algmaal\LaravelFawaterak\Exceptions\FawaterakException;
use Algmaal\LaravelFawaterak\Exceptions\InvalidPaymentDataException;

class PaymentService implements PaymentServiceInterface
{
    protected FawaterakServiceInterface $fawaterakService;

    public function __construct(FawaterakServiceInterface $fawaterakService)
    {
        $this->fawaterakService = $fawaterakService;
    }

    /**
     * Create a new payment.
     */
    public function createPayment(
        array $customerData,
        array $cartItems,
        float $total,
        int $paymentMethodId,
        array $options = []
    ): array {
        $this->validateCustomerData($customerData);
        $this->validateCartItems($cartItems);

        $paymentData = [
            'payment_method_id' => $paymentMethodId,
            'cartTotal' => (string) $total,
            'currency' => $options['currency'] ?? config('fawaterak.default_currency', 'EGP'),
            'customer' => $customerData,
            'cartItems' => $cartItems,
        ];

        // Add optional fields
        if (isset($options['invoice_number'])) {
            $paymentData['invoice_number'] = $options['invoice_number'];
        }

        if (isset($options['redirection_urls'])) {
            $paymentData['redirectionUrls'] = $options['redirection_urls'];
        } else {
            $paymentData['redirectionUrls'] = $this->getDefaultRedirectionUrls();
        }

        if (isset($options['frequency'])) {
            $paymentData['frequency'] = $options['frequency'];
        }

        if (isset($options['custom_expire_date'])) {
            $paymentData['customExpireDate'] = $options['custom_expire_date'];
        }

        if (isset($options['discount_data'])) {
            $paymentData['discountData'] = $options['discount_data'];
        }

        if (isset($options['tax_data'])) {
            $paymentData['taxData'] = $options['tax_data'];
        }

        if (isset($options['auth_and_capture'])) {
            $paymentData['authAndCapture'] = $options['auth_and_capture'];
        }

        if (isset($options['payload'])) {
            $paymentData['payLoad'] = $options['payload'];
        }

        if (isset($options['mobile_wallet_number'])) {
            $paymentData['mobileWalletNumber'] = $options['mobile_wallet_number'];
        }

        if (isset($options['due_date'])) {
            $paymentData['due_date'] = $options['due_date'];
        }

        if (isset($options['send_email'])) {
            $paymentData['sendEmail'] = $options['send_email'];
        }

        if (isset($options['send_sms'])) {
            $paymentData['sendSMS'] = $options['send_sms'];
        }

        if (isset($options['lang'])) {
            $paymentData['lang'] = $options['lang'];
        }

        if (isset($options['redirect_option'])) {
            $paymentData['redirectOption'] = $options['redirect_option'];
        }

        return $this->fawaterakService->initiatePayment($paymentData);
    }

    /**
     * Get payment by invoice key.
     */
    public function getPayment(string $invoiceKey): array
    {
        return $this->fawaterakService->getTransactionStatus($invoiceKey);
    }

    /**
     * Check if payment is successful.
     */
    public function isPaymentSuccessful(string $invoiceKey): bool
    {
        try {
            $response = $this->getPayment($invoiceKey);
            
            if (isset($response['data']['invoice_status'])) {
                return strtolower($response['data']['invoice_status']) === 'paid';
            }

            return false;
        } catch (FawaterakException $e) {
            return false;
        }
    }

    /**
     * Get payment status.
     */
    public function getPaymentStatus(string $invoiceKey): string
    {
        try {
            $response = $this->getPayment($invoiceKey);
            
            return $response['data']['invoice_status'] ?? 'unknown';
        } catch (FawaterakException $e) {
            return 'error';
        }
    }

    /**
     * Process webhook data.
     */
    public function processWebhook(array $webhookData): array
    {
        // Validate webhook data structure
        if (!isset($webhookData['invoice_key'])) {
            throw new FawaterakException('Invalid webhook data: missing invoice_key');
        }

        // Get the latest payment status
        $paymentData = $this->getPayment($webhookData['invoice_key']);

        return [
            'invoice_key' => $webhookData['invoice_key'],
            'status' => $paymentData['data']['invoice_status'] ?? 'unknown',
            'payment_data' => $paymentData['data'] ?? [],
            'webhook_data' => $webhookData,
        ];
    }

    /**
     * Validate customer data.
     */
    protected function validateCustomerData(array $customerData): void
    {
        $required = ['first_name', 'last_name', 'email'];
        
        foreach ($required as $field) {
            if (!isset($customerData[$field]) || empty($customerData[$field])) {
                throw new InvalidPaymentDataException("Customer field '{$field}' is required");
            }
        }

        if (!filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidPaymentDataException('Invalid email address');
        }
    }

    /**
     * Validate cart items.
     */
    protected function validateCartItems(array $cartItems): void
    {
        if (empty($cartItems)) {
            throw new InvalidPaymentDataException('Cart items cannot be empty');
        }

        foreach ($cartItems as $index => $item) {
            if (!isset($item['name']) || empty($item['name'])) {
                throw new InvalidPaymentDataException("Cart item {$index}: name is required");
            }

            if (!isset($item['price']) || !is_numeric($item['price']) || $item['price'] <= 0) {
                throw new InvalidPaymentDataException("Cart item {$index}: valid price is required");
            }

            if (!isset($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                throw new InvalidPaymentDataException("Cart item {$index}: valid quantity is required");
            }
        }
    }

    /**
     * Get default redirection URLs.
     */
    protected function getDefaultRedirectionUrls(): array
    {
        return [
            'successUrl' => url(config('fawaterak.default_urls.success_url', '/payment/success')),
            'failUrl' => url(config('fawaterak.default_urls.fail_url', '/payment/failed')),
            'pendingUrl' => url(config('fawaterak.default_urls.pending_url', '/payment/pending')),
        ];
    }
}
