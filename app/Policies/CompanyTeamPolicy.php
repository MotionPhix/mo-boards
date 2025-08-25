<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use App\Services\Billing\PlanGate;

class CompanyTeamPolicy
{
    public function viewAny(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company);
    }

    public function view(User $user, Company $company, User $member): bool
    {
        return $user->canAccessCompany($company);
    }

    public function update(User $user, Company $company, User $member): bool
    {
        if (!$user->canAccessCompany($company)) {
            return false;
        }

        // Owner and manager can update non-owner team members
        if (!$member->pivot->is_owner) {
            return $user->isOwnerOf($company) ||
                   in_array($user->getRoleInCompany($company), ['company_owner', 'manager']);
        }

        // Only owner can update other owners
        return $user->isOwnerOf($company);
    }

    public function delete(User $user, Company $company, User $member): bool
    {
        if (!$user->canAccessCompany($company)) {
            return false;
        }

        // Can't delete yourself
        if ($user->id === $member->id) {
            return false;
        }

        // Can't delete owners
        if ($member->pivot->is_owner) {
            return false;
        }

        // Owner and manager can delete team members
        return $user->isOwnerOf($company) ||
               in_array($user->getRoleInCompany($company), ['company_owner', 'manager']);
    }

    public function invite(User $user, Company $company): bool
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

    public function manageInvitations(User $user, Company $company): bool
    {
        return $user->canAccessCompany($company) &&
               ($user->isOwnerOf($company) ||
                in_array($user->getRoleInCompany($company), ['company_owner', 'manager']));
    }
}
