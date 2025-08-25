<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;
use App\Models\User;
use App\Services\Billing\PlanGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class CompanyController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        // Server-side pagination, sorting and filtering
        $query = $user->companies()
            ->withPivot('is_owner', 'joined_at')
            ->withCount('billboards')
            ->select('companies.*');

        // Filters
        if ($q = request()->query('q')) {
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('name', 'like', "%{$q}%")
                    ->orWhere('industry', 'like', "%{$q}%");
            });
        }

        if ($plan = request()->query('plan')) {
            if ($plan !== 'all') {
                $query->where('subscription_plan', $plan);
            }
        }

        // Sorting
        $allowedSorts = ['name', 'created_at', 'updated_at', 'billboards_count'];
        $sort = in_array(request()->query('sort', 'name'), $allowedSorts) ? request()->query('sort', 'name') : 'name';
        $direction = request()->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if ($sort === 'billboards_count') {
            $query->orderByRaw("(SELECT COUNT(*) FROM billboards WHERE billboards.company_id = companies.id) $direction");
        } else {
            $query->orderBy($sort, $direction);
        }

        $perPage = (int) request()->query('per_page', 12);
        $companies = $query->paginate($perPage)->appends(request()->query());

        return Inertia::render('companies/Index', [
            'companies' => $companies,
            'currentCompany' => $user->currentCompany,
            'canCreateCompany' => $this->canUserCreateCompany($user),
        ]);
    }

    public function destroy(Company $company)
    {
        $user = Auth::user();

        $this->authorize('delete', $company);

        // Prevent deleting current company
        if ($user->current_company_id === $company->id) {
            return back()->with('error', 'Cannot delete the currently active company.');
        }

        $company->delete();

        return back()->with('success', 'Company deleted.');
    }

    public function bulkDestroy()
    {
        $user = Auth::user();

        $ids = request()->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No companies selected.');
        }

        $companies = Company::whereIn('id', $ids)->get();

        foreach ($companies as $company) {
            if ($company->id === $user->current_company_id) {
                continue; // skip current
            }

            if ($user->can('delete', $company)) {
                $company->delete();
            }
        }

        return back()->with('success', 'Selected companies deleted where permitted.');
    }

    public function create(): Response|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        // Authorize using the CompanyPolicy (boolean). Keep detailed messages from helper for UX.
        if (!$user->can('create', Company::class)) {
            $authResult = $this->getDetailedCompanyCreationAuthorization($user);

            return back()
                ->with('error', $authResult['message'])
                ->with('upgrade_required', $authResult['upgrade_required'] ?? false);
        }

        // Render as a modal page
        return Inertia::render('companies/CreateModal');
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $user = Auth::user();

        // Authorize using the CompanyPolicy (boolean). Keep detailed messages from helper for UX.
        if (!$user->can('create', Company::class)) {
            $authResult = $this->getDetailedCompanyCreationAuthorization($user);

            return redirect()->route('companies.index')
                ->with('error', $authResult['message'])
                ->with('upgrade_required', $authResult['upgrade_required'] ?? false);
        }

        $company = Company::create($request->validated());

        // Attach user as owner
        $company->users()->attach($user->id, [
            'is_owner' => true,
            'role' => 'company_owner',
            'joined_at' => now(),
        ]);

        // Assign company owner role
        $user->assignRole('company_owner');

        // Set as current company
        $user->update(['current_company_id' => $company->id]);

        return redirect()->route('dashboard')
            ->with('success', 'Company created successfully!');
    }

    /**
     * Check if user can create a company (simple boolean check)
     */
    private function canUserCreateCompany(User $user): bool
    {
        $authResult = $this->getDetailedCompanyCreationAuthorization($user);
        return $authResult['authorized'];
    }

    /**
     * Get detailed authorization result for company creation
     */
    private function getDetailedCompanyCreationAuthorization(User $user): array
    {
        // Get user's current plan from their primary company or default to free
        $primaryCompany = $user->companies()->wherePivot('is_owner', true)->first();
        $planId = $primaryCompany?->subscription_plan ?? 'free';

        // Get current company count
        $currentCompanyCount = $user->companies()->wherePivot('is_owner', true)->count();

        // Get plan limit for companies
        $companyLimit = PlanGate::limit($planId, 'companies.max');

        // Check if plan allows creating companies
        if ($companyLimit === '0' || $companyLimit === 0) {
            return [
                'authorized' => false,
                'reason' => 'plan_restriction',
                'message' => "Company creation is not available on the {$planId} plan. Upgrade to create additional companies.",
                'upgrade_required' => true,
                'current_plan' => $planId,
                'company_count' => $currentCompanyCount,
                'company_limit' => 0,
            ];
        }

        // Check if at company limit
        if ($companyLimit !== 'unlimited' && $currentCompanyCount >= (int) $companyLimit) {
            $message = $companyLimit == 1
                ? "You can only have 1 company on the {$planId} plan. Upgrade to create additional companies."
                : "You've reached your company limit ({$companyLimit}) for the {$planId} plan. Upgrade to create more companies.";

            return [
                'authorized' => false,
                'reason' => 'company_limit_reached',
                'message' => $message,
                'upgrade_required' => true,
                'current_plan' => $planId,
                'company_count' => $currentCompanyCount,
                'company_limit' => (int) $companyLimit,
            ];
        }

        return [
            'authorized' => true,
            'reason' => null,
            'message' => null,
            'current_plan' => $planId,
            'company_count' => $currentCompanyCount,
            'company_limit' => $companyLimit === 'unlimited' ? null : (int) $companyLimit,
        ];
    }
}
