<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanySettingsRequest;
use App\Models\Company;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
  use AuthorizesRequests;

  public function index(): Response
  {
    $user = Auth::user();
    $companies = $user->companies()
      ->withPivot('is_owner', 'joined_at')
      ->withCount('billboards')
      ->get();

    return Inertia::render('companies/Index', [
      'companies' => $companies,
      'currentCompany' => $user->currentCompany,
    ]);
  }

  public function create(): Response
  {
    return Inertia::render('companies/Create');
  }

  public function store(StoreCompanyRequest $request)
  {
    $user = Auth::user();

    $company = Company::create($request->validated());

    // Attach user as owner
    $company->users()->attach($user->id, [
      'is_owner' => true,
      'joined_at' => now(),
    ]);

    // Assign company owner role
    $user->assignRole('company_owner');

    // Set as current company
    $user->update(['current_company_id' => $company->id]);

    return redirect()->route('dashboard')
      ->with('success', 'Company created successfully!');
  }

  public function switchCompany(Request $request, Company $company)
  {
    $user = Auth::user();

    if (!$user->canAccessCompany($company)) {
      abort(403, 'You do not have access to this company.');
    }

    $user->update(['current_company_id' => $company->id]);

    return redirect()->back()
      ->with('success', "Switched to {$company->name}");
  }

  public function settings(): Response
  {
    $this->authorize('manageSettings', Auth::user()->currentCompany);

    $company = Auth::user()->currentCompany;

    // Get available options for dropdowns
    $timezones = $this->getAvailableTimezones();
    $currencies = $this->getAvailableCurrencies();
    $dateFormats = $this->getAvailableDateFormats();
    $timeFormats = $this->getAvailableTimeFormats();

    return Inertia::render('companies/Settings', [
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

  public function updateSettings(UpdateCompanySettingsRequest $request)
  {
    $company = Auth::user()->currentCompany;
    $data = $request->validated();

    // Handle logo upload using Spatie Media Library
    if ($request->hasFile('logo')) {
      // Clear existing logo (Spatie Media Library handles this automatically for single file collections)
      $company->clearMediaCollection('logo');

      // Add new logo to the media collection
      $company->addMediaFromRequest('logo')
        ->toMediaCollection('logo');
    }

    // Remove logo from data array since it's handled by media library
    unset($data['logo']);

    // Update company settings
    $company->update($data);

    return redirect()->route('companies.settings')
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
      'Y-m-d' => date('Y-m-d') . ' (YYYY-MM-DD)',
      'm/d/Y' => date('m/d/Y') . ' (MM/DD/YYYY)',
      'd/m/Y' => date('d/m/Y') . ' (DD/MM/YYYY)',
      'M d, Y' => date('M d, Y') . ' (Mon DD, YYYY)',
      'F j, Y' => date('F j, Y') . ' (Month DD, YYYY)',
    ];

    return collect($formats)->map(function ($label, $value) {
      return ['value' => $value, 'label' => $label];
    })->values()->toArray();
  }

  private function getAvailableTimeFormats(): array
  {
    $formats = [
      'H:i' => date('H:i') . ' (24-hour)',
      'g:i A' => date('g:i A') . ' (12-hour)',
      'h:i A' => date('h:i A') . ' (12-hour with leading zero)',
    ];

    return collect($formats)->map(function ($label, $value) {
      return ['value' => $value, 'label' => $label];
    })->values()->toArray();
  }
}
