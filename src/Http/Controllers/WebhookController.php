<?php

namespace Algmaal\LaravelFawaterak\Http\Controllers;

use Algmaal\LaravelFawaterak\Contracts\FawaterakServiceInterface;
use Algmaal\LaravelFawaterak\Contracts\PaymentServiceInterface;
use Algmaal\LaravelFawaterak\Exceptions\FawaterakException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected FawaterakServiceInterface $fawaterakService;
    protected PaymentServiceInterface $paymentService;

    public function __construct(
        FawaterakServiceInterface $fawaterakService,
        PaymentServiceInterface $paymentService
    ) {
        $this->fawaterakService = $fawaterakService;
        $this->paymentService = $paymentService;
    }

    /**
     * Handle incoming webhook from Fawaterak.
     */
    public function handle(Request $request): Response
    {
        try {
            // Get the raw payload
            $payload = $request->getContent();
            $signature = $request->header('X-Fawaterak-Signature', '');

            // Verify webhook signature if enabled
            if (config('fawaterak.webhook.verify_signature', true)) {
                if (!$this->fawaterakService->verifyWebhookSignature($payload, $signature)) {
                    Log::warning('Fawaterak webhook signature verification failed', [
                        'signature' => $signature,
                        'payload_length' => strlen($payload),
                    ]);

                    return response('Unauthorized', 401);
                }
            }

            // Parse the webhook data
            $webhookData = json_decode($payload, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Fawaterak webhook invalid JSON payload', [
                    'error' => json_last_error_msg(),
                    'payload' => $payload,
                ]);

                return response('Bad Request', 400);
            }

            // Log the webhook
            Log::info('Fawaterak webhook received', $webhookData);

            // Process the webhook
            $processedData = $this->paymentService->processWebhook($webhookData);

            // Fire webhook event
            event('fawaterak.webhook.received', [$processedData]);

            // Fire specific status events
            $status = $processedData['status'] ?? 'unknown';
            event("fawaterak.payment.{$status}", [$processedData]);

            Log::info('Fawaterak webhook processed successfully', [
                'invoice_key' => $processedData['invoice_key'] ?? 'unknown',
                'status' => $status,
            ]);

            return response('OK', 200);

        } catch (FawaterakException $e) {
            Log::error('Fawaterak webhook processing failed', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'context' => $e->getContext(),
            ]);

            return response('Internal Server Error', 500);

        } catch (\Exception $e) {
            Log::error('Unexpected error processing Fawaterak webhook', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response('Internal Server Error', 500);
        }
    }
}
