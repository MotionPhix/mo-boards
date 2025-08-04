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
}
