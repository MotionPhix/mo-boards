<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCompanySettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manageSettings', $this->user()->currentCompany);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $section = $this->input('section');

        // Base optional fields shared across sections
        $profileRules = [
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'company_description' => 'nullable|string|max:1000',
        ];

        $numberingRules = [
            'contract_number_prefix' => 'nullable|string|max:10',
            'contract_number_suffix' => 'nullable|string|max:10',
            'contract_number_format' => 'required|in:sequential,date_based,custom',
            'contract_number_length' => 'required|integer|min:3|max:10',
            'contract_number_start' => 'required|integer|min:1',
            'billboard_code_prefix' => 'nullable|string|max:10',
            'billboard_code_suffix' => 'nullable|string|max:10',
            'billboard_code_format' => 'required|in:sequential,location_based,custom',
            'billboard_code_length' => 'required|integer|min:2|max:8',
            'billboard_code_start' => 'required|integer|min:1',
        ];

        $notificationRules = [
            'notification_settings' => 'required|array',
            'notification_settings.email_contracts' => 'boolean',
            'notification_settings.email_payments' => 'boolean',
            'notification_settings.email_billboards' => 'boolean',
            'notification_settings.email_team' => 'boolean',
            'notification_settings.slack_webhook' => 'nullable|url',
        ];

        $socialRules = [
            'social_media_links' => 'required|array',
            'social_media_links.facebook' => 'nullable|url',
            'social_media_links.twitter' => 'nullable|url',
            'social_media_links.linkedin' => 'nullable|url',
            'social_media_links.instagram' => 'nullable|url',
        ];

        $businessRules = [
            'timezone' => 'required|string|max:100',
            'currency' => 'required|string|size:3',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
            'payment_terms_days' => 'required|integer|min:1|max:365',
            'late_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'auto_generate_invoices' => 'boolean',
            'tax_id' => 'nullable|string|max:100',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
        ];

        return match ($section) {
            'numbering' => $numberingRules,
            'notifications' => $notificationRules,
            'social' => $socialRules,
            'business' => $businessRules,
            default => $profileRules, // 'profile' or unspecified
        };
    }

    public function messages(): array
    {
        return [
            'logo.image' => 'The logo must be an image file.',
            'logo.mimes' => 'The logo must be a JPEG, PNG, JPG, GIF, or SVG file.',
            'logo.max' => 'The logo file size must not exceed 2MB.',
            'contract_number_format.in' => 'Contract number format must be sequential, date_based, or custom.',
            'billboard_code_format.in' => 'Billboard code format must be sequential, location_based, or custom.',
            'currency.size' => 'Currency must be a 3-letter ISO code (e.g., USD, EUR).',
            'payment_terms_days.min' => 'Payment terms must be at least 1 day.',
            'payment_terms_days.max' => 'Payment terms cannot exceed 365 days.',
        ];
    }
}
