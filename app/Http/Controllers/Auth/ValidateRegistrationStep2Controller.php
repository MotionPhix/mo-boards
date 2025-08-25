<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

final class ValidateRegistrationStep2Controller extends Controller
{
    public function __invoke(Request $request)
    {
        // Check if step 1 was completed
        if (!Session::has('registration_step1') || Session::get('registration_progress') < 1) {
            return redirect()->route('register')->with('error', 'Please complete step 1 first');
        }

        try {
            $validated = $request->validate([
                'company_name' => 'required|string|max:255',
                'industry' => 'nullable|string|in:outdoor-advertising,marketing-agency,real-estate,retail,other',
                'company_size' => 'nullable|string|in:1-10,11-50,51-200,200+',
                'address' => 'nullable|string|max:1000',
            ]);

            // Store validated data in session
            Session::put('registration_step2', $validated);
            Session::put('registration_progress', 2);

            return redirect()->route('register.plan')->with($validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
