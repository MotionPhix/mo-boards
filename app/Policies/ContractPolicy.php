<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('contracts.view_any');
    }

    public function view(User $user, Contract $contract): bool
    {
        return $user->can('contracts.view') &&
               $user->canAccessCompany($contract->company);
    }

    public function create(User $user): bool
    {
        return $user->can('contracts.create');
    }

    public function update(User $user, Contract $contract): bool
    {
        return $user->can('contracts.update') &&
               $user->canAccessCompany($contract->company);
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $user->can('contracts.delete') &&
               $user->canAccessCompany($contract->company);
    }

    public function approve(User $user, Contract $contract): bool
    {
        return $user->can('contracts.approve') &&
               $user->canAccessCompany($contract->company);
    }

    public function cancel(User $user, Contract $contract): bool
    {
        return $user->can('contracts.cancel') &&
               $user->canAccessCompany($contract->company);
    }

    public function managePayments(User $user, Contract $contract): bool
    {
        return $user->can('contracts.manage_payments') &&
               $user->canAccessCompany($contract->company);
    }

    public function viewFinancial(User $user, Contract $contract): bool
    {
        return $user->can('contracts.view_financial') &&
               $user->canAccessCompany($contract->company);
    }
}
