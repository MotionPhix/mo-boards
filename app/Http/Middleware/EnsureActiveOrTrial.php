<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\CompanySubscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureActiveOrTrial
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $company = $user?->currentCompany;
        if (!$company) {
            return $next($request);
        }

        $sub = CompanySubscription::where('company_id', $company->id)->first();
        $now = now();
        $graceDays = (int) config('billing.grace_days', 7);

        // Treat missing subscription as free/active
        if (!$sub) {
            return $next($request);
        }

        $status = $sub->status; // trial, active, past_due, unpaid, canceled, grace
        $periodEnd = $sub->current_period_end ?? $company->subscription_expires_at;

        $inTrial = $status === 'trial' && $periodEnd && $now->lt($periodEnd);
        $isActive = $status === 'active';
        $inGrace = ($status === 'past_due' || $status === 'unpaid') && $periodEnd && $now->lt($periodEnd->copy()->addDays($graceDays));

        if ($inTrial || $isActive || $inGrace) {
            return $next($request);
        }

        // Redirect to billing if outside of trial/active/grace
        return redirect()->route(config('billing.routes.checkout'), ['plan' => $company->subscription_plan]);
    }
}
