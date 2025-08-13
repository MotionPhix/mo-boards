<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BillingPlan;
use App\Models\Company;
use App\Models\CompanyBillingAudit;
use App\Models\CompanySubscription;
use App\Models\CompanyTransaction;
use App\Services\PayChangu\PayChanguService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

final class PayChanguWebhookController extends Controller
{
    public function __invoke(Request $request, PayChanguService $payChangu)
    {
        $signature = (string) $request->header('X-PayChangu-Signature', '');
        $payload = $request->getContent();

        if ($signature && ! $payChangu->validateWebhookSignature($payload, $signature)) {
            Log::warning('Invalid PayChangu webhook signature');

            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $data = $request->all();
        $event = $data['event'] ?? null; // e.g., payment.succeeded, payment.failed
        $payment = $data['payment'] ?? [];
        $metadata = $payment['metadata'] ?? [];
        $companyId = $metadata['company_id'] ?? null;
        $planId = $metadata['plan_id'] ?? null;

        if (! $companyId) {
            Log::warning('Webhook missing company_id');

            return response()->json(['message' => 'ok']);
        }

        $company = Company::find($companyId);
        if (! $company) {
            return response()->json(['message' => 'ok']);
        }

        if ($event === 'payment.succeeded') {
            $reference = $payment['reference'] ?? null;
            $paymentId = $payment['payment_id'] ?? null;
            $amount = (int) ($payment['amount'] ?? 0);
            $currency = (string) ($payment['currency'] ?? 'MWK');
            $paidAt = $payment['paid_at'] ?? null;

            // Update transaction
            CompanyTransaction::where('company_id', $company->id)
                ->where(function ($q) use ($reference, $paymentId) {
                    $q->when($paymentId, fn ($qq) => $qq->where('payment_id', $paymentId))
                        ->when($reference, fn ($qq) => $qq->orWhere('reference', $reference));
                })
                ->latest()
                ->take(1)
                ->update([
                    'status' => 'paid',
                    'occurred_at' => $paidAt ? Carbon::parse($paidAt) : now(),
                ]);

            if ($planId) {
                $bp = BillingPlan::where('key', $planId)->first();
                $company->update(['subscription_plan' => $planId]);
                CompanySubscription::create([
                    'company_id' => $company->id,
                    'plan_id' => $planId,
                    'plan_name' => $bp?->name ?? ucfirst($planId),
                    'price' => $amount,
                    'currency' => $currency,
                    'status' => 'active',
                    'started_at' => now(),
                    'ends_at' => $bp ? now()->add($bp->interval, $bp->interval_count) : null,
                    'meta' => $metadata,
                ]);

                CompanyBillingAudit::create([
                    'company_id' => $company->id,
                    'actor_id' => null,
                    'action' => 'payment_succeeded',
                    'details' => [
                        'plan_id' => $planId,
                        'payment_id' => $paymentId,
                        'reference' => $reference,
                    ],
                ]);
            }
        } elseif ($event === 'payment.failed') {
            $reference = $payment['reference'] ?? null;
            $paymentId = $payment['payment_id'] ?? null;

            CompanyTransaction::where('company_id', $company->id)
                ->where(function ($q) use ($reference, $paymentId) {
                    $q->when($paymentId, fn ($qq) => $qq->where('payment_id', $paymentId))
                        ->when($reference, fn ($qq) => $qq->orWhere('reference', $reference));
                })
                ->latest()
                ->take(1)
                ->update(['status' => 'failed']);

            CompanyBillingAudit::create([
                'company_id' => $company->id,
                'actor_id' => null,
                'action' => 'payment_failed',
                'details' => $payment,
            ]);
        }

        return Response::json(['message' => 'ok']);
    }
}
