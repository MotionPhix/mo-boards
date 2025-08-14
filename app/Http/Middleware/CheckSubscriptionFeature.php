<?php

namespace App\Http\Middleware;

use App\Enums\SubscriptionFeature;
use App\Services\SubscriptionFeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionFeature
{
    public function __construct(
        private SubscriptionFeatureService $subscriptionFeatureService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();
        
        if (!$user || !$user->current_company_id) {
            return redirect()->route('companies.index')
                ->with('error', 'Please select a company to access this feature.');
        }

        $company = $user->currentCompany;
        
        if (!$company) {
            return redirect()->route('companies.index')
                ->with('error', 'Invalid company selected.');
        }

        $subscriptionFeature = SubscriptionFeature::tryFrom($feature);
        
        if (!$subscriptionFeature) {
            // If feature doesn't exist, allow access (fail open)
            return $next($request);
        }

        if (!$this->subscriptionFeatureService->hasFeature($company, $subscriptionFeature)) {
            return $this->handleUnauthorizedFeature($request, $subscriptionFeature, $company);
        }

        return $next($request);
    }

    private function handleUnauthorizedFeature(Request $request, SubscriptionFeature $feature, $company): Response
    {
        $message = $this->getFeatureUpgradeMessage($feature);
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'feature' => $feature->value,
                'current_plan' => $company->subscription_plan ?? 'free',
                'upgrade_required' => true
            ], 403);
        }

        return redirect()->back()
            ->with('error', $message)
            ->with('upgrade_required', true);
    }

    private function getFeatureUpgradeMessage(SubscriptionFeature $feature): string
    {
        return match($feature) {
            SubscriptionFeature::TEAM_INVITATIONS => 'Team invitations are available with Pro and Business plans. Upgrade to invite team members.',
            SubscriptionFeature::UNLIMITED_TEAM_MEMBERS => 'Unlimited team members are available with the Business plan.',
            SubscriptionFeature::ADVANCED_ANALYTICS => 'Advanced analytics are available with Pro and Business plans.',
            SubscriptionFeature::ADVANCED_INSIGHTS => 'Advanced insights are available with the Business plan.',
            SubscriptionFeature::API_ACCESS => 'API access is available with the Business plan.',
            SubscriptionFeature::EXPORT_FUNCTIONALITY => 'Export functionality is available with the Business plan.',
            SubscriptionFeature::BULK_OPERATIONS => 'Bulk operations are available with the Business plan.',
            SubscriptionFeature::SMS_NOTIFICATIONS => 'SMS notifications are available with the Business plan.',
            default => "This feature requires a higher subscription plan. Please upgrade to access {$feature->getDescription()}."
        };
    }
}
