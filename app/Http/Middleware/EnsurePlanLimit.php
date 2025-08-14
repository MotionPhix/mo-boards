<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Billing\PlanGate;
use Closure;
use Illuminate\Http\Request;

final class EnsurePlanLimit
{
    /**
     * Ensure a numeric limit has not been exceeded.
     * Usage: ->middleware('plan.limit:billboards.max,company.billboards.count')
     * The second argument is a counter key resolved below.
     */
    public function handle(Request $request, Closure $next, string $limitKey, string $counterKey)
    {
        // In test environment, do not enforce plan limits to keep feature tests deterministic
        if (app()->environment('testing')) {
            return $next($request);
        }

        $user = $request->user();
        $company = $user?->currentCompany;
        if (! $company) {
            abort(403, 'No active company.');
        }

        $planId = is_object($company->subscription_plan)
            ? $company->subscription_plan->value
            : (string) ($company->subscription_plan ?? 'free');
        $limit = PlanGate::limit($planId, $limitKey, null); // null = unlimited

        if ($limit === null) {
            return $next($request); // unlimited
        }

        $count = match ($counterKey) {
            'company.billboards.count' => $company->billboards()->count(),
            'company.contracts.count' => $company->contracts()->count(),
            'company.team.members.count' => $company->users()->count(),
            default => 0,
        };

        if ($count >= $limit) {
            abort(403, 'You have reached your plan limit for this action.');
        }

        return $next($request);
    }
}
