<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Company extends Model implements HasMedia
{
  use HasFactory, InteractsWithMedia;

  protected $fillable = [
    'name',
    'slug',
    'industry',
    'size',
    'address',
    'subscription_plan',
    'subscription_expires_at',
    'is_active',
  ];

  protected $casts = [
    'subscription_expires_at' => 'datetime',
    'is_active' => 'boolean',
  ];

  protected static function booted(): void
  {
    static::creating(function (Company $company) {
      if (empty($company->slug)) {
        $company->slug = Str::slug($company->name);
      }
    });
  }

  public function users(): BelongsToMany
  {
    return $this->belongsToMany(User::class)
      ->withPivot('is_owner', 'joined_at')
      ->withTimestamps();
  }

  public function owners(): BelongsToMany
  {
    return $this->users()->wherePivot('is_owner', true);
  }

  public function billboards(): HasMany
  {
    return $this->hasMany(Billboard::class);
  }

  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('logo')
      ->singleFile()
      ->acceptsMimeTypes(['image/jpeg', 'image/png']);
  }

  public function contracts(): HasMany
  {
    return $this->hasMany(Contract::class);
  }

  public function contractTemplates(): HasMany
  {
    return $this->hasMany(ContractTemplate::class);
  }

  public function scopeWithActiveContracts($query)
  {
    return $query->whereHas('contracts', function ($q) {
      $q->where('status', 'active');
    });
  }
}
