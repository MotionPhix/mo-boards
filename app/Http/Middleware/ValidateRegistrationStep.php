<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

final class ValidateRegistrationStep
{
    public function handle(Request $request, Closure $next, int $requiredStep)
    {
        $currentProgress = Session::get('registration_progress', 0);

        // Allow going back to previous steps
        if ($requiredStep <= $currentProgress + 1) {
            return $next($request);
        }

        // If trying to skip steps, redirect to the appropriate step
        $stepRoutes = [
            1 => 'register',
            2 => 'register.company',
            3 => 'register.plan',
        ];

        $nextStep = $currentProgress + 1;
        if (isset($stepRoutes[$nextStep])) {
            return redirect()->route($stepRoutes[$nextStep])->with('error', 'Please complete the registration steps in order.');
        }

        return redirect()->route('register');
    }
}
