<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class ValidationErrorResponse
{
    public function toResponse(ValidationException $exception): Response
    {
        return Inertia::render('auth/Register', [
            'step' => session('registration.current_step', 1),
            'errors' => $exception->errors(),
            'message' => $exception->getMessage(),
            'data' => session()->getOldInput(),
        ]);
    }
}
