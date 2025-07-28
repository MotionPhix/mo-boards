<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
  public function index(): Response
  {
    $user = Auth::user();
    $companies = $user->companies()
      ->withPivot('is_owner', 'joined_at')
      ->withCount('billboards')
      ->get();

    return Inertia::render('Companies/Index', [
      'companies' => $companies,
      'currentCompany' => $user->currentCompany,
    ]);
  }

  public function create(): Response
  {
    return Inertia::render('Companies/Create');
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
}
