<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

final class ValidateRegistrationStep1Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
                'phone' => 'nullable|string|max:20',
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            return response()->json([
                'valid' => true,
                'message' => 'Step 1 validation successful',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'valid' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed for step 1',
            ], 422);
        } catch (Exception $e) {
            Log::error('Step 1 validation error', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? 'unknown',
            ]);

            return response()->json([
                'valid' => false,
                'errors' => ['general' => ['An unexpected error occurred during validation']],
                'message' => 'Server error',
            ], 500);
        }
    }
}
