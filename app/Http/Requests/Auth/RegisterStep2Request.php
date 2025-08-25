<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterStep2Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'industry' => ['required', 'string', 'in:outdoor-advertising,marketing-agency,real-estate,retail,other'],
            'company_size' => ['required', 'string', 'in:1-10,11-50,51-200,200+'],
            'address' => ['required', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // max 2MB
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => 'Please provide your company name',
            'company_name.max' => 'Company name cannot exceed 255 characters',
            'industry.required' => 'Please select your industry',
            'industry.in' => 'Please select a valid industry',
            'company_size.required' => 'Please select your company size',
            'company_size.in' => 'Please select a valid company size',
            'address.required' => 'Please provide your company address',
            'address.max' => 'Address cannot exceed 1000 characters',
            'logo.image' => 'The logo must be an image',
            'logo.mimes' => 'The logo must be a file of type: jpeg, png, jpg, gif',
            'logo.max' => 'The logo must not be larger than 2MB',
        ];
    }
}
