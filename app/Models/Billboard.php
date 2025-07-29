<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Billboard extends Model implements HasMedia
{
  use HasFactory, InteractsWithMedia;

  protected $fillable = [
    'company_id',
    'name',
    'code',
    'location',
    'latitude',
    'longitude',
    'width',
    'height',
    'monthly_rate',
    'status',
    'description',
  ];

  protected $casts = [
    'latitude' => 'decimal:8',
    'longitude' => 'decimal:8',
    'width' => 'decimal:2',
    'height' => 'decimal:2',
    'monthly_rate' => 'decimal:2',
  ];

  protected static function booted(): void
  {
    static::creating(function (Billboard $billboard) {
      if (empty($billboard->code)) {
        $billboard->code = 'BB' . str_pad(
            Billboard::where('company_id', $billboard->company_id)->count() + 1,
            3,
            '0',
            STR_PAD_LEFT
          );
      }
    });
  }

  public function company(): BelongsTo
  {
    return $this->belongsTo(Company::class);
  }

  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('images')
      ->acceptsMimeTypes(['image/jpeg', 'image/png']);

    $this->addMediaCollection('documents')
      ->acceptsMimeTypes(['application/pdf']);
  }

  public function contracts(): BelongsToMany
  {
    return $this->belongsToMany(Contract::class, 'contract_billboards')
      ->withPivot('rate', 'notes')
      ->withTimestamps();
  }

  public function activeContracts()
  {
    return $this->contracts()->where('status', 'active');
  }

// Add these scopes to Billboard model:
  public function scopeAvailable($query, $startDate = null, $endDate = null)
  {
    // Billboards that don't have active contracts in the given date range
    return $query->whereDoesntHave('contracts', function ($q) use ($startDate, $endDate) {
      $q->where('status', 'active');
      if ($startDate && $endDate) {
        $q->where(function ($dateQuery) use ($startDate, $endDate) {
          $dateQuery->whereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate])
            ->orWhere(function ($overlapQuery) use ($startDate, $endDate) {
              $overlapQuery->where('start_date', '<=', $startDate)
                ->where('end_date', '>=', $endDate);
            });
        });
      }
    });
  }

  public function scopeContracted($query)
  {
    return $query->whereHas('activeContracts');
  }

  // Check if billboard is available for given date range
  public function isAvailableForPeriod($startDate, $endDate): bool
  {
    return !$this->contracts()
      ->where('status', 'active')
      ->where(function ($query) use ($startDate, $endDate) {
        $query->whereBetween('start_date', [$startDate, $endDate])
          ->orWhereBetween('end_date', [$startDate, $endDate])
          ->orWhere(function ($overlapQuery) use ($startDate, $endDate) {
            $overlapQuery->where('start_date', '<=', $startDate)
              ->where('end_date', '>=', $endDate);
          });
      })
      ->exists();
  }

  public function getStatusColor(): string
  {
    return match ($this->status) {
      'active' => 'success',
      'inactive' => 'warning',
      'maintenance' => 'destructive',
      default => 'secondary',
    };
  }

  public function isCurrentlyOccupied(): bool
  {
    return $this->activeContracts()->exists();
  }

  public function formatFileSize(int $bytes): string
  {
    $units = ['B', 'KB', 'MB', 'GB'];
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
      $bytes /= 1024;
    }
    return round($bytes, 2) . ' ' . $units[$i];
  }

  // Generate a formatted size string from width and height
  public function getSizeAttribute(): ?string
  {
    if ($this->width && $this->height) {
      return $this->width . ' x ' . $this->height;
    }
    return null;
  }
}
