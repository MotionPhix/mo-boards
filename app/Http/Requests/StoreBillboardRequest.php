<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillboardRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => 'required|string|max:255',
      'location' => 'required|string|max:500',
      'latitude' => 'nullable|numeric|between:-90,90',
      'longitude' => 'nullable|numeric|between:-180,180',
      'size' => 'nullable|string|max:50',
      'width' => 'nullable|numeric|min:0',
      'height' => 'nullable|numeric|min:0',
      'monthly_rate' => 'nullable|numeric|min:0',
      'status' => 'required|in:active,inactive,maintenance',
      'description' => 'nullable|string|max:1000',
    ];
  }
}
