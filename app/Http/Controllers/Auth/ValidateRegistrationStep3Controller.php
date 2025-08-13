<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class ValidateRegistrationStep3Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'subscription_plan' => 'required|string|in:starter,professional,enterprise',
            ]);

            return response()->json([
                'valid' => true,
                'message' => 'Step 3 validation successful',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'valid' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed for step 3',
            ], 422);
        } catch (Exception $e) {
            Log::error('Step 3 validation error', [
                'error' => $e->getMessage(),
                'subscription_plan' => $request->subscription_plan ?? 'unknown',
            ]);

            return response()->json([
                'valid' => false,
                'errors' => ['general' => ['An unexpected error occurred during validation']],
                'message' => 'Server error',
            ], 500);
        }
    }
}
