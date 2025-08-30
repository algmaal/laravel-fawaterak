<?php

namespace Algmaal\LaravelFawaterak\Services;

use Algmaal\LaravelFawaterak\Contracts\FawaterakServiceInterface;
use Algmaal\LaravelFawaterak\Exceptions\FawaterakException;
use Algmaal\LaravelFawaterak\Exceptions\InvalidConfigurationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FawaterakService implements FawaterakServiceInterface
{
    protected Client $httpClient;
    protected array $config;
    protected string $environment;
    protected array $environmentConfig;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->environment = $config['default'] ?? 'staging';
        $this->environmentConfig = $config['environments'][$this->environment] ?? [];
        
        $this->validateConfiguration();
        $this->initializeHttpClient();
    }

    /**
     * Get available payment methods.
     */
    public function getPaymentMethods(): array
    {
        $cacheKey = $this->getCacheKey('payment_methods');
        
        if ($this->isCacheEnabled() && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = $this->makeRequest('GET', $this->config['endpoints']['payment_methods']);
        
        if ($this->isCacheEnabled()) {
            Cache::put($cacheKey, $response, $this->getCacheTtl());
        }

        return $response;
    }

    /**
     * Initiate a payment.
     */
    public function initiatePayment(array $paymentData): array
    {
        $this->validatePaymentData($paymentData);
        
        return $this->makeRequest('POST', $this->config['endpoints']['initiate_payment'], $paymentData);
    }

    /**
     * Get transaction status.
     */
    public function getTransactionStatus(string $invoiceKey): array
    {
        return $this->makeRequest('POST', $this->config['endpoints']['transaction_status'], [
            'invoice_key' => $invoiceKey
        ]);
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (!$this->config['webhook']['verify_signature']) {
            return true;
        }

        $secret = $this->environmentConfig['webhook_secret'] ?? '';
        if (empty($secret)) {
            throw new InvalidConfigurationException('Webhook secret is not configured');
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Make HTTP request to Fawaterak API.
     */
    public function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getApiKey(),
                ],
                'timeout' => $this->config['http']['timeout'],
                'connect_timeout' => $this->config['http']['connect_timeout'],
                'verify' => $this->config['http']['verify'],
            ];

            if (!empty($data)) {
                $options['json'] = $data;
            }

            $url = $this->getBaseUrl() . $endpoint;

            $this->logRequest($method, $url, $data);

            $response = $this->httpClient->request($method, $url, $options);
            $responseData = json_decode($response->getBody()->getContents(), true);

            $this->logResponse($responseData);

            if (!isset($responseData['status']) || $responseData['status'] !== 'success') {
                throw new FawaterakException(
                    $responseData['message'] ?? 'Unknown error occurred',
                    $response->getStatusCode()
                );
            }

            return $responseData;

        } catch (GuzzleException $e) {
            $this->logError($e);
            throw new FawaterakException('HTTP request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Validate configuration.
     */
    protected function validateConfiguration(): void
    {
        if (empty($this->environmentConfig)) {
            throw new InvalidConfigurationException("Environment '{$this->environment}' is not configured");
        }

        if (empty($this->environmentConfig['api_key'])) {
            throw new InvalidConfigurationException("API key is not configured for environment '{$this->environment}'");
        }

        if (empty($this->environmentConfig['base_url'])) {
            throw new InvalidConfigurationException("Base URL is not configured for environment '{$this->environment}'");
        }
    }

    /**
     * Initialize HTTP client.
     */
    protected function initializeHttpClient(): void
    {
        $this->httpClient = new Client();
    }

    /**
     * Validate payment data.
     */
    protected function validatePaymentData(array $data): void
    {
        $required = ['payment_method_id', 'cartTotal', 'currency', 'customer', 'cartItems'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new FawaterakException("Required field '{$field}' is missing");
            }
        }

        if (!in_array($data['currency'], $this->config['supported_currencies'])) {
            throw new FawaterakException("Unsupported currency: {$data['currency']}");
        }
    }

    /**
     * Get API key for current environment.
     */
    protected function getApiKey(): string
    {
        return $this->environmentConfig['api_key'];
    }

    /**
     * Get base URL for current environment.
     */
    protected function getBaseUrl(): string
    {
        return $this->environmentConfig['base_url'];
    }

    /**
     * Check if cache is enabled.
     */
    protected function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? false;
    }

    /**
     * Get cache TTL.
     */
    protected function getCacheTtl(): int
    {
        return $this->config['cache']['ttl'] ?? 3600;
    }

    /**
     * Get cache key.
     */
    protected function getCacheKey(string $key): string
    {
        $prefix = $this->config['cache']['prefix'] ?? 'fawaterak';
        return "{$prefix}:{$this->environment}:{$key}";
    }

    /**
     * Log request.
     */
    protected function logRequest(string $method, string $url, array $data): void
    {
        if ($this->isLoggingEnabled() && $this->config['logging']['log_requests']) {
            Log::channel($this->getLogChannel())->info('Fawaterak API Request', [
                'method' => $method,
                'url' => $url,
                'data' => $data,
            ]);
        }
    }

    /**
     * Log response.
     */
    protected function logResponse(array $response): void
    {
        if ($this->isLoggingEnabled() && $this->config['logging']['log_responses']) {
            Log::channel($this->getLogChannel())->info('Fawaterak API Response', $response);
        }
    }

    /**
     * Log error.
     */
    protected function logError(\Throwable $e): void
    {
        if ($this->isLoggingEnabled()) {
            Log::channel($this->getLogChannel())->error('Fawaterak API Error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    /**
     * Check if logging is enabled.
     */
    protected function isLoggingEnabled(): bool
    {
        return $this->config['logging']['enabled'] ?? true;
    }

    /**
     * Get log channel.
     */
    protected function getLogChannel(): string
    {
        return $this->config['logging']['channel'] ?? 'default';
    }
}
