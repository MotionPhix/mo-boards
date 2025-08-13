<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('companies.view_any');
    }

    public function view(User $user, Company $company): bool
    {
        return $user->can('companies.view') && $user->canAccessCompany($company);
    }

    public function create(User $user): bool
    {
        return $user->can('companies.create');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->can('companies.update') && $user->canAccessCompany($company);
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->can('companies.delete') && $user->canAccessCompany($company);
    }

    public function switch(User $user): bool
    {
        return $user->can('companies.switch');
    }

    public function manageSettings(User $user, Company $company): bool
    {
        return $user->can('companies.manage_settings') && $user->canAccessCompany($company);
    }

    public function viewBilling(User $user, Company $company): bool
    {
        return $user->can('companies.view_billing') && $user->canAccessCompany($company);
    }

    public function manageBilling(User $user, Company $company): bool
    {
        return $user->can('companies.manage_billing') && $user->canAccessCompany($company);
    }

    // Team management abilities within the company context
    public function inviteTeamMember(User $user, Company $company): bool
    {
        return $user->can('team.invite') && $user->canAccessCompany($company);
    }

    public function updateTeamMember(User $user, Company $company): bool
    {
        return $user->can('team.update_roles') && $user->canAccessCompany($company);
    }

    public function updateTeamPermissions(User $user, Company $company): bool
    {
        return $user->can('team.update_permissions') && $user->canAccessCompany($company);
    }

    public function removeTeamMember(User $user, Company $company): bool
    {
        return $user->can('team.remove') && $user->canAccessCompany($company);
    }

    public function viewTeamMembers(User $user, Company $company): bool
    {
        return $user->can('team.view_any') && $user->canAccessCompany($company);
    }

    public function viewTeamMember(User $user, Company $company): bool
    {
        return $user->can('team.view') && $user->canAccessCompany($company);
    }

    public function manageInvitations(User $user, Company $company): bool
    {
        return $user->can('team.manage_invitations') && $user->canAccessCompany($company);
    }

    public function viewTeamActivity(User $user, Company $company): bool
    {
        return $user->can('team.view_activity') && $user->canAccessCompany($company);
    }
}
