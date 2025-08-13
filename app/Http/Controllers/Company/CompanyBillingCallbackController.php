<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\BillingPlan;
use App\Models\CompanyBillingAudit;
use App\Models\CompanySubscription;
use App\Models\CompanyTransaction;
use App\Services\PayChangu\PayChanguService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

final class CompanyBillingCallbackController extends Controller
{
    public function __invoke(Request $request, PayChanguService $payChangu): RedirectResponse
    {
        $paymentId = $request->query('payment_id');
        if (! $paymentId) {
            return redirect()->route('companies.settings.billing')->with('error', 'Missing payment reference');
        }

        try {
            $verification = $payChangu->verifyPayment($paymentId);
            $meta = $verification->metadata ?? [];
            $companyId = $meta['company_id'] ?? null;
            $planId = $meta['plan_id'] ?? null;

            $company = Auth::user()->currentCompany;
            if ($companyId && $company && (int) $companyId === (int) $company->id && $verification->isPaid() && $planId) {
                $company->update([
                    'subscription_plan' => $planId,
                ]);

                CompanyTransaction::where('company_id', $company->id)
                    ->where(function ($q) use ($verification) {
                        $q->where('payment_id', $verification->payment_id)
                            ->orWhere('reference', $verification->reference);
                    })
                    ->latest()
                    ->take(1)
                    ->update([
                        'status' => 'paid',
                        'raw_response' => [
                            'verification' => [
                                'status' => $verification->status,
                                'paid_at' => $verification->paid_at,
                            ],
                        ],
                        'occurred_at' => $verification->paid_at ? now()->parse($verification->paid_at) : now(),
                    ]);

                $bp = BillingPlan::where('key', $planId)->first();
                CompanySubscription::create([
                    'company_id' => $company->id,
                    'plan_id' => $planId,
                    'plan_name' => $bp?->name ?? ucfirst($planId),
                    'price' => (int) $verification->amount,
                    'currency' => (string) $verification->currency,
                    'status' => 'active',
                    'started_at' => now(),
                    'ends_at' => $bp ? now()->add($bp->interval, $bp->interval_count) : null,
                    'meta' => $verification->metadata,
                ]);

                CompanyBillingAudit::create([
                    'company_id' => $company->id,
                    'actor_id' => Auth::id(),
                    'action' => 'payment_succeeded',
                    'details' => [
                        'plan_id' => $planId,
                        'payment_id' => $verification->payment_id,
                        'reference' => $verification->reference,
                    ],
                ]);

                return redirect()->route('companies.settings.billing')->with('success', 'Subscription activated');
            }

            return redirect()->route('companies.settings.billing')->with('error', 'Payment not verified');
        } catch (Throwable $e) {
            CompanyTransaction::where('payment_id', $paymentId)->update(['status' => 'failed']);
            if (Auth::user()?->currentCompany) {
                CompanyBillingAudit::create([
                    'company_id' => Auth::user()->currentCompany->id,
                    'actor_id' => Auth::id(),
                    'action' => 'payment_failed',
                    'details' => [
                        'payment_id' => $paymentId,
                        'error' => $e->getMessage(),
                    ],
                ]);
            }

            return redirect()->route('companies.settings.billing')->with('error', 'Payment verification failed');
        }
    }
}
