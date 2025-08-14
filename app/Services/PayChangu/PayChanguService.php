<?php

declare(strict_types=1);

namespace App\Services\PayChangu;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class PayChanguService
{
    private string $baseUrl;

    private string $clientId;

    private string $clientSecret;

    private string $callbackUrl;

    public function __construct()
    {
        $this->baseUrl = (string) config('paychangu.base_url', 'https://api.paychangu.com');
        $this->clientId = (string) (config('paychangu.client_id') ?? '');
        $this->clientSecret = (string) (config('paychangu.client_secret') ?? '');
        $this->callbackUrl = url(config('paychangu.callback_url'));
    }

    /**
     * Create a payment request
     */
    public function createPayment(PayChanguPaymentRequest $paymentRequest): PayChanguPaymentResponse
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->getAccessToken(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(mb_rtrim($this->baseUrl, '/').'/api/v1/payments', [
                'amount' => $paymentRequest->amount,
                'currency' => $paymentRequest->currency,
                'reference' => $paymentRequest->reference,
                'callback_url' => $paymentRequest->callbackUrl ?? $this->callbackUrl,
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
                    'response' => $response->body(),
                ]);
                throw new Exception('Payment creation failed: '.$response->body());
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
                'trace' => $e->getTraceAsString(),
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
                'Authorization' => 'Bearer '.$this->getAccessToken(),
                'Accept' => 'application/json',
            ])->get(mb_rtrim($this->baseUrl, '/')."/api/v1/payments/{$paymentId}");

            if ($response->failed()) {
                Log::error('PayChangu payment verification failed', [
                    'payment_id' => $paymentId,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                throw new Exception('Payment verification failed: '.$response->body());
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
                'trace' => $e->getTraceAsString(),
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

    /**
     * Get access token for API authentication
     */
    private function getAccessToken(): string
    {
        $cacheKey = 'paychangu_access_token';

        if ($token = cache($cacheKey)) {
            return $token;
        }

        if ($this->clientId === '' || $this->clientSecret === '') {
            throw new RuntimeException('PayChangu credentials are not configured. Set PAYCHANGU_CLIENT_ID and PAYCHANGU_CLIENT_SECRET in your .env.');
        }

        try {
            $base = mb_rtrim($this->baseUrl, '/');
            $endpoints = [
                $base.'/api/v1/oauth/token',
                $base.'/oauth/token',
            ];

            $payload = [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'payments',
            ];

            $last = null;
            foreach ($endpoints as $url) {
                $res = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post($url, $payload);

                if ($res->successful()) {
                    $data = $res->json();
                    $token = $data['access_token'] ?? null;
                    if ($token) {
                        $ttl = (int) ($data['expires_in'] ?? 3600);
                        cache([$cacheKey => $token], now()->addSeconds(max(60, $ttl) - 60));

                        return $token;
                    }
                }

                if (in_array($res->status(), [404, 405], true) || str_contains(mb_strtolower($res->body()), 'post method is not supported')) {
                    $res = Http::withHeaders(['Accept' => 'application/json'])->get($url, $payload);
                    if ($res->successful()) {
                        $data = $res->json();
                        $token = $data['access_token'] ?? null;
                        if ($token) {
                            $ttl = (int) ($data['expires_in'] ?? 3600);
                            cache([$cacheKey => $token], now()->addSeconds(max(60, $ttl) - 60));

                            return $token;
                        }
                    }
                }

                $last = ['status' => $res->status(), 'response' => $res->body(), 'url' => $url];
                Log::warning('PayChangu token attempt failed', $last);
            }

            $msg = $last ? ($last['status'].' '.$last['response']) : 'Unknown error';
            throw new Exception('Failed to get access token: '.$msg);
        } catch (Exception $e) {
            Log::error('PayChangu token error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
