<?php

namespace App\Policies;

use App\Models\Billboard;
use App\Models\User;

class BillboardPolicy
{
  public function viewAny(User $user): bool
  {
    return $user->can('view billboards');
  }

  public function view(User $user, Billboard $billboard): bool
  {
    return $user->can('view billboards') &&
      $user->canAccessCompany($billboard->company);
  }

  public function create(User $user): bool
  {
    return $user->can('create billboards');
  }

  public function update(User $user, Billboard $billboard): bool
  {
    return $user->can('edit billboards') &&
      $user->canAccessCompany($billboard->company);
  }

  public function delete(User $user, Billboard $billboard): bool
  {
    return $user->can('delete billboards') &&
      $user->canAccessCompany($billboard->company);
  }
}
