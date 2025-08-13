<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanySettingsRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class CompanySettingsController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('manageSettings', Auth::user()->currentCompany);

        $company = Auth::user()->currentCompany;

        // Get available options for dropdowns
        $timezones = $this->getAvailableTimezones();
        $currencies = $this->getAvailableCurrencies();
        $dateFormats = $this->getAvailableDateFormats();
        $timeFormats = $this->getAvailableTimeFormats();

        // Choose a route-specific settings page component
        $routeName = $request->route()?->getName();
        $component = match ($routeName) {
            'companies.settings.profile', 'companies.settings' => 'companies/settings/Profile',
            'companies.settings.numbering' => 'companies/settings/Numbering',
            'companies.settings.notifications' => 'companies/settings/Notifications',
            'companies.settings.social' => 'companies/settings/Social',
            'companies.settings.business' => 'companies/settings/Business',
            default => 'companies/settings/Profile',
        };

        return Inertia::render($component, [
            'company' => [
                ...$company->toArray(),
                'logo_url' => $company->logo_url,
                'has_logo' => $company->hasLogo(),
                'next_contract_number_preview' => $company->getNextContractNumberPreview(),
                'next_billboard_code_preview' => $company->getNextBillboardCodePreview(),
            ],
            'options' => [
                'timezones' => $timezones,
                'currencies' => $currencies,
                'dateFormats' => $dateFormats,
                'timeFormats' => $timeFormats,
                'contractNumberFormats' => [
                    ['value' => 'sequential', 'label' => 'Sequential (1, 2, 3...)'],
                    ['value' => 'date_based', 'label' => 'Date-based (YYYYMM001)'],
                    ['value' => 'custom', 'label' => 'Custom Format'],
                ],
                'billboardCodeFormats' => [
                    ['value' => 'sequential', 'label' => 'Sequential (1, 2, 3...)'],
                    ['value' => 'location_based', 'label' => 'Location-based (LOC001)'],
                    ['value' => 'custom', 'label' => 'Custom Format'],
                ],
            ],
        ]);
    }

    public function update(UpdateCompanySettingsRequest $request)
    {
        $company = Auth::user()->currentCompany;
        $data = $request->validated();

        // Handle logo upload using Spatie Media Library
        if ($request->hasFile('logo')) {
            $company->clearMediaCollection('logo');
            $company->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        unset($data['logo']);

        $company->update($data);

        $section = $request->input('section');
        $route = match ($section) {
            'numbering' => 'companies.settings.numbering',
            'notifications' => 'companies.settings.notifications',
            'social' => 'companies.settings.social',
            'business' => 'companies.settings.business',
            default => 'companies.settings.profile',
        };

        return redirect()->route($route)
            ->with('success', 'Company settings updated successfully!');
    }

    private function getAvailableTimezones(): array
    {
        $timezones = [
            'UTC' => 'UTC (Coordinated Universal Time)',
            'Africa/Blantyre' => 'Blantyre',
            'Africa/Cairo' => 'Cairo',
            'Africa/Casablanca' => 'Casablanca',
            'Africa/Johannesburg' => 'Johannesburg',
            'America/New_York' => 'Eastern Time (US & Canada)',
            'America/Chicago' => 'Central Time (US & Canada)',
            'America/Denver' => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Europe/Berlin' => 'Berlin',
            'Asia/Tokyo' => 'Tokyo',
            'Asia/Shanghai' => 'Shanghai',
            'Australia/Sydney' => 'Sydney',
            'Asia/Dubai' => 'Dubai',
            'Asia/Kolkata' => 'Kolkata',
            'America/Sao_Paulo' => 'Sao Paulo',
            'America/Mexico_City' => 'Mexico City',
        ];

        return collect($timezones)->map(function ($label, $value) {
            return ['value' => $value, 'label' => $label];
        })->values()->toArray();
    }

    private function getAvailableCurrencies(): array
    {
        return \App\Helpers\CurrencyHelper::getDropdownOptions();
    }

    private function getAvailableDateFormats(): array
    {
        $formats = [
            'Y-m-d' => date('Y-m-d').' (YYYY-MM-DD)',
            'm/d/Y' => date('m/d/Y').' (MM/DD/YYYY)',
            'd/m/Y' => date('d/m/Y').' (DD/MM/YYYY)',
            'M d, Y' => date('M d, Y').' (Mon DD, YYYY)',
            'F j, Y' => date('F j, Y').' (Month DD, YYYY)',
        ];

        return collect($formats)->map(function ($label, $value) {
            return ['value' => $value, 'label' => $label];
        })->values()->toArray();
    }

    private function getAvailableTimeFormats(): array
    {
        $formats = [
            'H:i' => date('H:i').' (24-hour)',
            'g:i A' => date('g:i A').' (12-hour)',
            'h:i A' => date('h:i A').' (12-hour with leading zero)',
        ];

        return collect($formats)->map(function ($label, $value) {
            return ['value' => $value, 'label' => $label];
        })->values()->toArray();
    }
}
