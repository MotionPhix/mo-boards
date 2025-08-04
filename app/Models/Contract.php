<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\HasUuid;

class Contract extends Model implements HasMedia
{
  use HasFactory, InteractsWithMedia, HasUuid;

  protected $fillable = [
    'contract_number',
    'company_id',
    'created_by',
    'template_id',
    'client_name',
    'client_email',
    'client_phone',
    'client_address',
    'client_company',
    'start_date',
    'end_date',
    'total_amount',
    'monthly_amount',
    'design', // The editable HTML content with placeholders
    'content', // The final HTML content with placeholders replaced
    'currency',
    'exchange_rate',
    'payment_terms',
    'status',
    'terms_and_conditions',
    'custom_fields_data',
    'notes',
    'document_content',
    'signed_at',
    'signed_by',
    'custom_field_values',
  ];

  protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'total_amount' => 'decimal:2',
    'monthly_amount' => 'decimal:2',
    'exchange_rate' => 'decimal:6',
    'terms_and_conditions' => 'array',
    'custom_fields_data' => 'array',
    'custom_field_values' => 'array',
    'signed_at' => 'datetime',
  ];

  protected static function booted(): void
  {
    static::creating(function (Contract $contract) {
      // Auto-generate contract number if not provided
      if (empty($contract->contract_number) && $contract->company) {
        $settingsService = app(\App\Services\CompanySettingsService::class);
        $contract->contract_number = $settingsService->generateContractNumber($contract->company);
      }

      // Set default currency from company settings if not provided
      if (empty($contract->currency) && $contract->company) {
        $contract->currency = $contract->company->currency ?? 'USD';
      }

      // Set default exchange rate
      if (is_null($contract->exchange_rate)) {
        $contract->exchange_rate = 1.000000;
      }
    });
  }

  public function company(): BelongsTo
  {
    return $this->belongsTo(Company::class);
  }

  public function createdBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function template(): BelongsTo
  {
    return $this->belongsTo(ContractTemplate::class, 'template_id');
  }

  public function billboards(): BelongsToMany
  {
    return $this->belongsToMany(Billboard::class, 'contract_billboards')
      ->withPivot('rate', 'notes')
      ->withTimestamps();
  }

  public function payments(): HasMany
  {
    return $this->hasMany(ContractPayment::class);
  }

  // Generate unique contract number
  private static function generateContractNumber(int $companyId): string
  {
    $year = date('Y');
    $count = static::where('company_id', $companyId)
        ->whereYear('created_at', $year)
        ->count() + 1;

    return "CT-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
  }

  // Calculate total monthly rate from billboards
  public function calculateTotalRate(): float
  {
    return $this->billboards->sum('pivot.rate');
  }

  // Check if contract is currently active
  public function isActive(): bool
  {
    return $this->status === 'active' &&
      $this->start_date <= now() &&
      $this->end_date >= now();
  }

  // Get contract duration in months
  public function getDurationInMonths(): int
  {
    return $this->start_date->diffInMonths($this->end_date) + 1;
  }

  // Scopes
  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }

  public function scopeExpiringSoon($query, int $days = 30)
  {
    return $query->where('status', 'active')
      ->where('end_date', '<=', now()->addDays($days));
  }

  // Media collections for contract documents
  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('contract_documents')
      ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);

    $this->addMediaCollection('signed_contract')
      ->singleFile()
      ->acceptsMimeTypes(['application/pdf']);
  }
}
