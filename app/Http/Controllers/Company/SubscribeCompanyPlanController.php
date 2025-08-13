<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\BillingPlan;
use App\Models\CompanyBillingAudit;
use App\Models\CompanySubscription;
use App\Models\CompanyTransaction;
use App\Services\PayChangu\PayChanguPaymentRequest;
use App\Services\PayChangu\PayChanguService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

final class SubscribeCompanyPlanController extends Controller
{
    public function __invoke(Request $request, PayChanguService $payChangu): RedirectResponse
    {
        $company = Auth::user()->currentCompany;
        $this->authorize('manageBilling', $company);

        $validated = $request->validate([
            'plan_id' => 'required|string|exists:billing_plans,key',
        ]);

        $plan = BillingPlan::where('key', $validated['plan_id'])->firstOrFail();
        $amount = $plan->price;
        if ($amount <= 0) {
            $previous = $company->subscription_plan;
            $company->update(['subscription_plan' => 'free']);
            CompanySubscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->key,
                'plan_name' => $plan->name,
                'price' => $plan->price,
                'currency' => $plan->currency,
                'status' => 'active',
                'started_at' => now(),
                'ends_at' => now()->add($plan->interval, $plan->interval_count),
            ]);

            CompanyBillingAudit::create([
                'company_id' => $company->id,
                'actor_id' => Auth::id(),
                'action' => 'change_plan',
                'details' => ['from' => $previous, 'to' => 'free'],
            ]);

            return redirect()->route('companies.settings.billing')->with('success', 'Plan changed to Free');
        }

        $user = Auth::user();
        $reference = 'SUB-'.mb_strtoupper(Str::random(10));
        $currency = $plan->currency ?? config('paychangu.currency', 'MWK');

        $paymentRequest = PayChanguPaymentRequest::create([
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $reference,
            'callback_url' => route('webhooks.paychangu'),
            'return_url' => route('companies.settings.billing'),
            'cancel_url' => route('companies.settings.billing'),
            'customer_first_name' => $user->name,
            'customer_last_name' => '',
            'customer_email' => $user->email,
            'customer_phone' => $user->phone ?? null,
            'metadata' => [
                'type' => 'subscription',
                'company_id' => $company->id,
                'plan_id' => $plan->key,
            ],
        ]);

        $response = $payChangu->createPayment($paymentRequest);

        CompanyTransaction::create([
            'company_id' => $company->id,
            'type' => 'subscription',
            'payment_id' => $response->payment_id,
            'reference' => $response->reference,
            'amount' => (int) $response->amount,
            'currency' => (string) $response->currency,
            'status' => 'pending',
            'description' => 'Subscription purchase: '.mb_strtoupper($validated['plan_id']),
            'raw_response' => [
                'checkout_url' => $response->checkout_url,
                'status' => $response->status,
            ],
            'meta' => [
                'plan_id' => $plan->key,
                'interval' => $plan->interval,
                'interval_count' => $plan->interval_count,
            ],
            'occurred_at' => now(),
        ]);

        CompanyBillingAudit::create([
            'company_id' => $company->id,
            'actor_id' => Auth::id(),
            'action' => 'subscribe',
            'details' => ['plan_id' => $validated['plan_id'], 'reference' => $response->reference],
        ]);

        return redirect()->away($response->checkout_url);
    }
}
