<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBillboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->billboard);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('billboards')
                    ->where('company_id', $this->user()->current_company_id)
                    ->ignore($this->billboard->id)
            ],
            'location' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'width' => 'nullable|numeric|min:0|max:1000',
            'height' => 'nullable|numeric|min:0|max:1000',
            'monthly_rate' => 'nullable|numeric|min:0|max:999999.99',
            'status' => [
                'required',
                Rule::in(['active', 'inactive', 'maintenance'])
            ],
            'description' => 'nullable|string|max:1000',
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
