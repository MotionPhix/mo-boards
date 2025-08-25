<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

final class ValidateRegistrationStep3Controller extends Controller
{
    public function __construct(
        private readonly PlanService $planService
    ) {}

    public function __invoke(Request $request)
    {
        // Check if previous steps were completed
        if (!Session::has('registration_step1') || !Session::has('registration_step2') ||
            Session::get('registration_progress') < 2) {
            return redirect()->route('register.company')->with('error', 'Please complete previous steps first');
        }

        try {
            $validated = $request->validate([
                'subscription_plan' => [
                    'required',
                    'string',
                    'in:free,pro,business'
                ],
            ]);

            // Verify the selected plan exists in our database
            $plans = $this->planService->getPlans();
            if (!isset($plans[$validated['subscription_plan']])) {
                return back()->withErrors([
                    'subscription_plan' => 'Selected plan is not available'
                ]);
            }

            // Store validated data and complete registration progress
            Session::put('registration_step3', $validated);
            Session::put('registration_progress', 3);

            // Process the final registration
            return redirect()->route('register')->with($validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
