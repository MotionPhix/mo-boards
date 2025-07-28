<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
  use HasFactory, Notifiable, HasRoles;

  protected $fillable = [
    'name',
    'email',
    'password',
    'phone',
    'current_company_id',
    'last_active_at',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
    'last_active_at' => 'datetime',
    'password' => 'hashed',
  ];

  public function companies(): BelongsToMany
  {
    return $this->belongsToMany(Company::class)
      ->withPivot('is_owner', 'joined_at')
      ->withTimestamps();
  }

  public function currentCompany(): BelongsTo
  {
    return $this->belongsTo(Company::class, 'current_company_id');
  }

  public function ownedCompanies(): BelongsToMany
  {
    return $this->companies()->wherePivot('is_owner', true);
  }

  public function canAccessCompany(Company $company): bool
  {
    return $this->companies()->where('companies.id', $company->id)->exists();
  }

  public function isOwnerOf(Company $company): bool
  {
    return $this->companies()
      ->where('companies.id', $company->id)
      ->wherePivot('is_owner', true)
      ->exists();
  }
}
