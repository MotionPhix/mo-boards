<?php

namespace App\Services\PayChangu;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayChanguService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $callbackUrl;

    public function __construct()
    {
        $this->baseUrl = config('paychangu.base_url', 'https://api.paychangu.com');
        $this->clientId = config('paychangu.client_id');
        $this->clientSecret = config('paychangu.client_secret');
        $this->callbackUrl = url(config('paychangu.callback_url'));
    }

    /**
     * Create a payment request
     */
    public function createPayment(PayChanguPaymentRequest $paymentRequest): PayChanguPaymentResponse
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/api/v1/payments', [
                'amount' => $paymentRequest->amount,
                'currency' => $paymentRequest->currency,
                'reference' => $paymentRequest->reference,
                'callback_url' => $this->callbackUrl,
                'return_url' => $paymentRequest->returnUrl,
                'cancel_url' => $paymentRequest->cancelUrl,
                'customer' => [
                    'first_name' => $paymentRequest->customerFirstName,
                    'last_name' => $paymentRequest->customerLastName,
                    'email' => $paymentRequest->customerEmail,
                    'phone' => $paymentRequest->customerPhone,
                ],
                'metadata' => $paymentRequest->metadata,
            ]);

            if ($response->failed()) {
                Log::error('PayChangu payment creation failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new Exception('Payment creation failed: ' . $response->body());
            }

            $data = $response->json();

            return new PayChanguPaymentResponse([
                'payment_id' => $data['payment_id'],
                'reference' => $data['reference'],
                'checkout_url' => $data['checkout_url'],
                'status' => $data['status'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'created_at' => $data['created_at'],
            ]);

        } catch (Exception $e) {
            Log::error('PayChangu service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Verify a payment
     */
    public function verifyPayment(string $paymentId): PayChanguPaymentVerification
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Accept' => 'application/json',
            ])->get($this->baseUrl . "/api/v1/payments/{$paymentId}");

            if ($response->failed()) {
                Log::error('PayChangu payment verification failed', [
                    'payment_id' => $paymentId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new Exception('Payment verification failed: ' . $response->body());
            }

            $data = $response->json();

            return new PayChanguPaymentVerification([
                'payment_id' => $data['payment_id'],
                'reference' => $data['reference'],
                'status' => $data['status'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'customer' => $data['customer'],
                'metadata' => $data['metadata'] ?? [],
                'paid_at' => $data['paid_at'] ?? null,
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at'],
            ]);

        } catch (Exception $e) {
            Log::error('PayChangu verification error', [
                'payment_id' => $paymentId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get access token for API authentication
     */
    private function getAccessToken(): string
    {
        $cacheKey = 'paychangu_access_token';

        // Try to get cached token
        $token = cache($cacheKey);
        if ($token) {
            return $token;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'payments',
            ]);

            if ($response->failed()) {
                Log::error('PayChangu token request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new Exception('Failed to get access token: ' . $response->body());
            }

            $data = $response->json();
            $token = $data['access_token'];
            $expiresIn = $data['expires_in'] ?? 3600;

            // Cache token for slightly less than expiry time
            cache([$cacheKey => $token], now()->addSeconds($expiresIn - 60));

            return $token;

        } catch (Exception $e) {
            Log::error('PayChangu token error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Validate webhook signature
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = config('paychangu.webhook_secret');
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }
}
