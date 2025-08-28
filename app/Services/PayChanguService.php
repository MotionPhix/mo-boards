<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\CompanyTransaction;
use App\Models\CompanyBillingAudit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class PayChanguService
{
    public function __construct()
    {
    }

    public function createCheckoutSession(Company $company, string $plan, string $successUrl, string $cancelUrl): string
    {
        // Map plan => amount/currency via DB or config('billing.paychangu.prices')
        $priceMap = config('billing.paychangu.prices');
        $priceId = $priceMap[$plan] ?? null;

        // Example payload; adapt to PayChangu API
        $payload = [
            'customer' => [
                'reference' => $company->id,
                'email' => $company->email ?? optional($company->users()->first())->email,
                'name' => $company->name,
            ],
            'plan' => $plan,
            'price_id' => $priceId,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];

        $res = Http::timeout((int) config('billing.paychangu.timeout', 15))
            ->withToken(config('billing.paychangu.api_key'))
            ->post(rtrim(config('billing.paychangu.base_url'), '/') . '/v1/checkout/sessions', $payload);

        if (!$res->successful()) {
            Log::error('PayChangu create checkout failed', ['status' => $res->status(), 'body' => $res->body()]);
            throw new \RuntimeException('Failed to create checkout session');
        }

        $data = $res->json();
        // Assume response contains a hosted url e.g., $data['url']
        return $data['url'] ?? throw new \RuntimeException('Checkout URL missing');
    }

    public function createBillingPortalSession(Company $company, string $returnUrl): string
    {
        $payload = [
            'customer_reference' => $company->id,
            'return_url' => $returnUrl,
        ];

        $res = Http::timeout((int) config('billing.paychangu.timeout', 15))
            ->withToken(config('billing.paychangu.api_key'))
            ->post(rtrim(config('billing.paychangu.base_url'), '/') . '/v1/billing/portal/sessions', $payload);

        if (!$res->successful()) {
            Log::error('PayChangu portal session failed', ['status' => $res->status(), 'body' => $res->body()]);
            throw new \RuntimeException('Failed to create portal session');
        }

        $data = $res->json();
        return $data['url'] ?? throw new \RuntimeException('Portal URL missing');
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        // Example HMAC verification; adapt per PayChangu docs
        $secret = config('billing.paychangu.webhook_secret');
        if (!$secret || !$signature) return false;

        $computed = hash_hmac('sha256', $payload, $secret);
        // Constant-time comparison
        return hash_equals($computed, $signature);
    }

    public function handleWebhookEvent(array $event): void
    {
        // Inspect event type and update subscription state accordingly
        $type = $event['type'] ?? null;
        $data = $event['data'] ?? [];

        match ($type) {
            'subscription.created', 'subscription.updated' => $this->handleSubscriptionUpdated($data),
            'invoice.payment_succeeded' => $this->handleInvoicePaid($data),
            'invoice.payment_failed' => $this->handleInvoiceFailed($data),
            default => Log::info('Unhandled PayChangu event', ['type' => $type]),
        };
    }

    private function handleSubscriptionUpdated(array $data): void
    {
        // Extract your identifiers from $data per PayChangu schema
        $companyId = $data['customer_reference'] ?? null;
        if (!$companyId) return;

        /** @var Company|null $company */
        $company = Company::find($companyId);
        if (!$company) return;

        $status = $data['status'] ?? 'active';
        $periodEnd = isset($data['current_period_end']) ? now()->parse($data['current_period_end']) : null;

        $sub = CompanySubscription::firstOrNew(['company_id' => $company->id]);
        $sub->plan = $data['plan'] ?? $sub->plan;
        $sub->status = $status;
        $sub->current_period_end = $periodEnd;
        $sub->external_subscription_id = $data['id'] ?? $sub->external_subscription_id;
        $sub->save();

        if ($periodEnd) {
            $company->subscription_expires_at = $periodEnd;
            $company->save();
        }

        CompanyBillingAudit::create([
            'company_id' => $company->id,
            'event' => 'subscription.updated',
            'metadata' => $data,
        ]);
    }

    private function handleInvoicePaid(array $data): void
    {
        $companyId = $data['customer_reference'] ?? null;
        if (!$companyId) return;
        $company = Company::find($companyId);
        if (!$company) return;

        CompanyTransaction::create([
            'company_id' => $company->id,
            'amount' => $data['amount'] ?? 0,
            'currency' => $data['currency'] ?? config('billing.currency'),
            'status' => 'paid',
            'type' => 'invoice',
            'external_id' => $data['id'] ?? null,
            'metadata' => $data,
        ]);

        CompanyBillingAudit::create([
            'company_id' => $company->id,
            'event' => 'invoice.payment_succeeded',
            'metadata' => $data,
        ]);
    }

    private function handleInvoiceFailed(array $data): void
    {
        $companyId = $data['customer_reference'] ?? null;
        if (!$companyId) return;
        $company = Company::find($companyId);
        if (!$company) return;

        CompanyTransaction::create([
            'company_id' => $company->id,
            'amount' => $data['amount'] ?? 0,
            'currency' => $data['currency'] ?? config('billing.currency'),
            'status' => 'failed',
            'type' => 'invoice',
            'external_id' => $data['id'] ?? null,
            'metadata' => $data,
        ]);

        CompanyBillingAudit::create([
            'company_id' => $company->id,
            'event' => 'invoice.payment_failed',
            'metadata' => $data,
        ]);
    }
}
