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
}
