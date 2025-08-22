<?php

namespace App\Policies;

use App\Models\Billboard;
use App\Models\User;

class BillboardPolicy
{
  public function viewAny(User $user): bool
  {
    // Allow all authenticated users to view billboards
    return true;
  }

  public function view(User $user, Billboard $billboard): bool
  {
    return $user->canAccessCompany($billboard->company);
  }

  public function create(User $user): bool
  {
    // Allow all authenticated users to create billboards
    return true;
  }

  public function update(User $user, Billboard $billboard): bool
  {
    return $user->canAccessCompany($billboard->company) &&
           in_array($user->getRoleInCompany($billboard->company), ['company_owner', 'manager', 'editor']);
  }

  public function delete(User $user, Billboard $billboard): bool
  {
    return $user->canAccessCompany($billboard->company) &&
           in_array($user->getRoleInCompany($billboard->company), ['company_owner', 'manager']);
  }

  public function duplicate(User $user, Billboard $billboard): bool
  {
    return $user->canAccessCompany($billboard->company) &&
           in_array($user->getRoleInCompany($billboard->company), ['company_owner', 'manager', 'editor']);
  }

  public function bulkUpdate(User $user): bool
  {
    // Allow company owners and managers to bulk update
    return true; // Will be filtered by company access in the controller
  }

  public function uploadMedia(User $user, Billboard $billboard): bool
  {
    return $user->canAccessCompany($billboard->company) &&
           in_array($user->getRoleInCompany($billboard->company), ['company_owner', 'manager', 'editor']);
  }

  public function manageMedia(User $user, Billboard $billboard): bool
  {
    return $user->canAccessCompany($billboard->company) &&
           in_array($user->getRoleInCompany($billboard->company), ['company_owner', 'manager', 'editor']);
  }

  public function viewAnalytics(User $user, Billboard $billboard): bool
  {
    return $user->canAccessCompany($billboard->company);
  }

  public function exportData(User $user): bool
  {
    // Allow all authenticated users - the middleware will handle subscription restrictions
    return true;
  }
}
