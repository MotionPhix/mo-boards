<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements HasMedia
{
  use HasFactory, Notifiable, HasRoles, InteractsWithMedia;

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
      ->withPivot('is_owner', 'joined_at', 'role')
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
  
  public function belongsToCompany(Company $company): bool
  {
    return $this->companies()->where('companies.id', $company->id)->exists();
  }
  
  public function getRoleInCompany(Company $company): ?string
  {
    $companyUser = $this->companies()
      ->where('companies.id', $company->id)
      ->first();
      
    return $companyUser ? $companyUser->pivot->role : null;
  }
  
  /**
   * Check if user has a specific permission within a company context
   */
  public function hasCompanyPermission(Company $company, string $permission): bool
  {
    // First check if user belongs to this company
    if (!$this->belongsToCompany($company)) {
      return false;
    }
    
    $role = $this->getRoleInCompany($company);
    
    // Super admin can do everything
    if ($this->hasRole('super_admin')) {
      return true;
    }
    
    // Company owners can do everything within their company
    if ($role === 'company_owner' || $this->isOwnerOf($company)) {
      return true;
    }
    
    // Check if the user has the specific permission through their role
    return $this->hasPermissionTo($permission);
  }

  public function registerMediaConversions(Media $media = null): void
  {
    $this->addMediaConversion('avatar')
      ->width(150)
      ->height(150)
      ->sharpen(10)
      ->performOnCollections('avatars');
  }

  public function getAvatarAttribute(): string
  {
    $avatar = $this->getFirstMediaUrl('avatars', 'avatar');

    if ($avatar) {
      return $avatar;
    }

    // Fallback to Gravatar
    $hash = md5(strtolower(trim($this->email)));
    return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=150";
  }
  
  /**
   * Get the URL of the user's profile photo.
   * This is an alias for the avatar attribute to maintain compatibility
   * with frameworks that expect this attribute name.
   *
   * @return string
   */
  public function getProfilePhotoUrlAttribute(): string
  {
    return $this->avatar;
  }
}
