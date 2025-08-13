<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserCanAccessCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // If user has a current company set, verify they can access it
        if ($user->current_company_id) {
            $canAccess = $user->companies()
                ->where('companies.id', $user->current_company_id)
                ->exists();

            if (! $canAccess) {
                // Reset current company if user can't access it
                $user->update(['current_company_id' => null]);
            }
        }

        // If no current company set, set to first available company
        if (! $user->current_company_id && $user->companies()->count() > 0) {
            $firstCompany = $user->companies()->first();
            $user->update(['current_company_id' => $firstCompany->id]);
        }

        return $next($request);
    }
}
