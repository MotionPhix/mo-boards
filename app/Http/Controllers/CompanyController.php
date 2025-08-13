<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class CompanyController extends Controller
{
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
        // Render as a modal page
        return Inertia::render('companies/CreateModal');
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
}
