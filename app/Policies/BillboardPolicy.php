<?php

namespace App\Policies;

use App\Models\Billboard;
use App\Models\User;

class BillboardPolicy
{
  public function viewAny(User $user): bool
  {
    return $user->can('billboards.view_any');
  }

  public function view(User $user, Billboard $billboard): bool
  {
    return $user->can('billboards.view') &&
      $user->canAccessCompany($billboard->company);
  }

  public function create(User $user): bool
  {
    return $user->can('billboards.create');
  }

  public function update(User $user, Billboard $billboard): bool
  {
    return $user->can('billboards.update') &&
      $user->canAccessCompany($billboard->company);
  }

  public function delete(User $user, Billboard $billboard): bool
  {
    return $user->can('billboards.delete') &&
      $user->canAccessCompany($billboard->company);
  }

  public function duplicate(User $user, Billboard $billboard): bool
  {
    return $user->can('billboards.duplicate') &&
      $user->canAccessCompany($billboard->company);
  }

  public function bulkUpdate(User $user): bool
  {
    return $user->can('billboards.bulk_update');
  }

  public function uploadMedia(User $user, Billboard $billboard): bool
  {
    return $user->can('billboards.upload_media') &&
      $user->canAccessCompany($billboard->company);
  }

  public function manageMedia(User $user, Billboard $billboard): bool
  {
    return $user->can('billboards.manage_media') &&
      $user->canAccessCompany($billboard->company);
  }

  public function viewAnalytics(User $user, Billboard $billboard): bool
  {
    return $user->can('billboards.view_analytics') &&
      $user->canAccessCompany($billboard->company);
  }

  public function exportData(User $user): bool
  {
    return $user->can('billboards.export_data');
  }
}
