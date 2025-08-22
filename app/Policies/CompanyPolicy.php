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
        // Allow all authenticated users to create companies
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

    // Team management abilities within the company context
    public function inviteTeamMember(User $user, Company $company): bool
    {
        // First check if user has access to the company and role permission
        if (!$user->canAccessCompany($company)) {
            return false;
        }

        $hasRolePermission = $user->isOwnerOf($company) ||
                            in_array($user->getRoleInCompany($company), ['company_owner', 'manager']);

        if (!$hasRolePermission) {
            return false;
        }

        // Then check subscription plan allows team invitations
        $planId = $company->subscription_plan ?? 'free';
        return PlanGate::allows($planId, 'team.invitations', false);
    }

    public function updateTeamMember(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company) &&
               ($user->isOwnerOf($company) ||
                in_array($user->getRoleInCompany($company), ['company_owner', 'manager']));
    }

    public function updateTeamPermissions(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company) &&
               ($user->isOwnerOf($company) ||
                $user->getRoleInCompany($company) === 'company_owner');
    }

    public function removeTeamMember(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company) &&
               ($user->isOwnerOf($company) ||
                in_array($user->getRoleInCompany($company), ['company_owner', 'manager']));
    }

    public function viewTeamMembers(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company);
    }

    public function viewTeamMember(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company);
    }

    public function manageInvitations(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company) &&
               ($user->isOwnerOf($company) ||
                in_array($user->getRoleInCompany($company), ['company_owner', 'manager']));
    }

    public function viewTeamActivity(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company) &&
               ($user->isOwnerOf($company) ||
                in_array($user->getRoleInCompany($company), ['company_owner', 'manager']));
    }
}
