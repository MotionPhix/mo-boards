<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use App\Services\Billing\PlanGate;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        // For now, allow all authenticated users to view companies
        return true;
    }

    public function view(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company);
    }

    public function create(User $user): bool
    {
        // Determine user's primary company (where they're an owner) to infer plan
        $primaryCompany = $user->companies()->wherePivot('is_owner', true)->first();
        $planId = $primaryCompany?->subscription_plan ?? 'free';

        // Get current company count where the user is owner
        $currentCompanyCount = $user->companies()->wherePivot('is_owner', true)->count();

        // Get plan limit for companies
        $companyLimit = PlanGate::limit($planId, 'companies.max');

        // If plan explicitly disallows companies (limit == 0)
        if ($companyLimit === '0' || $companyLimit === 0) {
            return false;
        }

        // If plan has a numeric limit and user has reached it
        if ($companyLimit !== 'unlimited' && $companyLimit !== null && $currentCompanyCount >= (int) $companyLimit) {
            return false;
        }

        return true;
    }

    public function update(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company) &&
               ($user->isOwnerOf($company) ||
                in_array($user->getRoleInCompany($company), ['company_owner', 'manager']));
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->isOwnerOf($company) ||
               $user->getRoleInCompany($company) === 'company_owner';
    }

    public function switch(User $user): bool
    {
        return true; // All users can switch between their companies
    }

    public function manageSettings(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company) &&
               ($user->isOwnerOf($company) ||
                in_array($user->getRoleInCompany($company), ['company_owner', 'manager']));
    }

    public function viewBilling(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company) &&
               ($user->isOwnerOf($company) ||
                $user->getRoleInCompany($company) === 'company_owner');
    }

    public function manageBilling(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company) &&
               ($user->isOwnerOf($company) ||
                $user->getRoleInCompany($company) === 'company_owner');
    }

    // Team activity permissions moved to CompanyTeamPolicy
}
