<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Propaganistas\LaravelPhone\Rules\Phone;

class RegisterStep1Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', new Phone()],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide your full name',
            'name.min' => 'Your name should be at least 3 characters long',
            'email.required' => 'Please provide your email address',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',
            'phone.required' => 'Please provide your phone number',
            'phone.*' => 'Please provide a valid phone number',
            'password.required' => 'Please create a password',
            'password.confirmed' => 'Password confirmation does not match',
        ];
    }
}
