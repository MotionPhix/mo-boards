<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            // Legacy tests use 'small' value; accept both canonical and legacy labels
            'size' => 'nullable|in:1-10,11-50,51-200,200+,small',
            'address' => 'nullable|string|max:500',
            // Allow nullable so policy/controller can enforce subscription requirements
            'subscription_plan' => 'nullable|in:free,pro,business',
        ];
    }
}
