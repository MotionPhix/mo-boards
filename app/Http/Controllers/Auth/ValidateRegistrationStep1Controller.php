<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

final class ValidateRegistrationStep1Controller extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
                'phone' => 'nullable|string|max:20',
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            // Store validated data in session for later use
            Session::put('registration_step1', $validated);
            Session::put('registration_progress', 1);

            return redirect()->route('register.company')->with($validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
