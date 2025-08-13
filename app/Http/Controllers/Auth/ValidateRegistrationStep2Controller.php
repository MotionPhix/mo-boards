<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class ValidateRegistrationStep2Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_name' => 'required|string|max:255',
                'industry' => 'nullable|string|in:outdoor-advertising,marketing-agency,real-estate,retail,other',
                'company_size' => 'nullable|string|in:1-10,11-50,51-200,200+',
                'address' => 'nullable|string|max:1000',
            ]);

            return response()->json([
                'valid' => true,
                'message' => 'Step 2 validation successful',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'valid' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed for step 2',
            ], 422);
        } catch (Exception $e) {
            Log::error('Step 2 validation error', [
                'error' => $e->getMessage(),
                'company_name' => $request->company_name ?? 'unknown',
            ]);

            return response()->json([
                'valid' => false,
                'errors' => ['general' => ['An unexpected error occurred during validation']],
                'message' => 'Server error',
            ], 500);
        }
    }
}
