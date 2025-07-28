<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
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
      'size' => 'nullable|in:1-10,11-50,51-200,200+',
      'address' => 'nullable|string|max:500',
      'subscription_plan' => 'required|in:starter,professional,enterprise',
    ];
  }
}
