<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanySubscription;
use App\Services\PayChanguService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

final class BillingController extends Controller
{
    public function __construct(private readonly PayChanguService $paychangu)
    {
    }

    // Start checkout for the current user's company and chosen plan
    public function checkout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $company = $user?->currentCompany;
        abort_unless($company, 404);

        $plan = $request->string('plan')->toString();
        abort_unless(in_array($plan, ['free', 'pro', 'business'], true), 422);

        // Build success/cancel URLs
        $successUrl = URL::route('dashboard');
        $cancelUrl = URL::route('settings.billing'); // adjust as needed

        $checkoutUrl = $this->paychangu->createCheckoutSession(
            company: $company,
            plan: $plan,
            successUrl: $successUrl,
            cancelUrl: $cancelUrl,
        );

        return redirect()->away($checkoutUrl);
    }

    // Redirect to billing portal for payment method management
    public function portal(): RedirectResponse
    {
        $user = Auth::user();
        $company = $user?->currentCompany;
        abort_unless($company, 404);

        $portalUrl = $this->paychangu->createBillingPortalSession($company, URL::route('settings.billing'));
        return redirect()->away($portalUrl);
    }

    // Webhook receiver for PayChangu
    public function webhook(Request $request): Response
    {
        // Validate signature if provided by PayChangu
        $payload = $request->getContent();
        $signature = $request->header('PayChangu-Signature');

        if (!$this->paychangu->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Invalid PayChangu webhook signature');
            return response('invalid signature', 400);
        }

        $event = $request->json()->all();
        try {
            $this->paychangu->handleWebhookEvent($event);
        } catch (\Throwable $e) {
            Log::error('PayChangu webhook handling error', ['error' => $e->getMessage()]);
            return response('error', 500);
        }

        return response('ok');
    }
}
