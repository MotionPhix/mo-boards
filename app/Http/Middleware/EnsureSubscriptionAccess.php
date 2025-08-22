<?php

namespace App\Http\Middleware;

use App\Services\Billing\PlanGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionAccess
{
    /**
     * Handle an incoming request.
     *
     * This middleware provides graceful handling of subscription-based access restrictions.
     * Instead of showing blank pages or throwing errors, it redirects users to appropriate
     * pages with helpful messages.
     */
    public function handle(Request $request, Closure $next, string $feature = null): Response
    {
        $user = $request->user();
        $company = $user?->currentCompany;

        if (!$company) {
            return redirect()->route('companies.index')
                ->with('error', 'Please select a company to continue.');
        }

        // If no specific feature is being checked, just continue
        if (!$feature) {
            return $next($request);
        }

        $planId = $company->subscription_plan ?? 'free';
        $hasAccess = PlanGate::allows($planId, $feature, false);

        if (!$hasAccess) {
            // Determine appropriate redirect and message based on the feature
            $redirectRoute = 'dashboard';
            $message = $this->getFeatureMessage($feature, $planId);

            // For team-related features, redirect to team page with upgrade message
            if (str_starts_with($feature, 'team.')) {
                $redirectRoute = 'team.index';
            }

            return redirect()->route($redirectRoute)
                ->with('error', $message)
                ->with('upgrade_required', true)
                ->with('required_feature', $feature)
                ->with('current_plan', $planId);
        }

        return $next($request);
    }

    /**
     * Get appropriate error message for the feature
     */
    private function getFeatureMessage(string $feature, string $planId): string
    {
        return match ($feature) {
            'team.invitations' => $planId === 'free'
                ? 'Team invitations are not available on the free plan. Upgrade to Pro or Business to invite team members.'
                : "You've reached your team invitation limit for the {$planId} plan. Please upgrade to invite more members.",

            'export.enabled' => 'Data export is only available on the Business plan. Upgrade to access this feature.',

            'analytics.advanced' => 'Advanced analytics are only available on Pro and Business plans. Upgrade to access detailed insights.',

            'bulk.operations' => 'Bulk operations are only available on the Business plan. Upgrade to manage multiple items at once.',

            default => "This feature is not available on your current plan ({$planId}). Please upgrade to access it.",
        };
    }
}
