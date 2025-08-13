<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Billing\PlanGate;
use Closure;
use Illuminate\Http\Request;

final class EnsurePlanFeature
{
    /**
     * Ensure the current company's plan allows a feature key.
     * Usage: ->middleware('plan.feature:analytics.access')
     */
    public function handle(Request $request, Closure $next, string $featureKey, string $default = 'false')
    {
        $user = $request->user();
        $company = $user?->currentCompany;
        if (! $company) {
            abort(403, 'No active company.');
        }

        $planId = (string) ($company->subscription_plan ?? 'free');
        $allowed = PlanGate::allows($planId, $featureKey, filter_var($default, FILTER_VALIDATE_BOOLEAN));

        if (! $allowed) {
            abort(403, 'Your plan does not allow this feature.');
        }

        return $next($request);
    }
}
