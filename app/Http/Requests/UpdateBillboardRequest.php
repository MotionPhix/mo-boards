<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\BillboardStatus;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

final class UpdateBillboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $billboard = request()->route('billboard');
        $user = Auth::user();

        return (bool) ($user?->can('update', $billboard) ?? false);
    }

    public function rules(): array
    {
        $billboard = request()->route('billboard');
        $user = Auth::user();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('billboards')
                    ->where('company_id', $user?->current_company_id)
                    ->ignore($billboard?->id),
            ],
            'location' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'width' => 'nullable|numeric|min:0|max:1000',
            'height' => 'nullable|numeric|min:0|max:1000',
            'monthly_rate' => 'nullable|numeric|min:0|max:999999.99',
            'status' => [
                'required',
                Rule::in(BillboardStatus::values()),
                // If current status is maintenance, only company_owner can change it
                function (string $attribute, mixed $value, Closure $fail) use ($billboard, $user) {
                    if (! $billboard) {
                        return;
                    }
                    $current = $billboard->status instanceof BillboardStatus ? $billboard->status->value : (string) $billboard->status;
                    $next = is_string($value) ? $value : (string) $value;
                    if ($current === BillboardStatus::MAINTENANCE->value && $next !== BillboardStatus::MAINTENANCE->value) {
                        if (! ($user?->hasRole('company_owner'))) {
                            $fail('Only a company owner can change status while in maintenance.');
                        }
                    }
                },
            ],
            'description' => 'nullable|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'file|image|mimes:jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A billboard with this name already exists in your company.',
            'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
            'width.max' => 'Width cannot exceed 1000 units.',
            'height.max' => 'Height cannot exceed 1000 units.',
            'monthly_rate.max' => 'Monthly rate cannot exceed $999,999.99.',
        ];
    }

    public function attributes(): array
    {
        return [
            'monthly_rate' => 'monthly rate',
        ];
    }
}
