<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Company extends Model implements HasMedia
{
    use HasFactory, HasUuid, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'industry',
        'size',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'phone',
        'email',
        'website',
        'subscription_plan',
        'subscription_expires_at',
        'is_active',
        'contract_number_prefix',
        'contract_number_suffix',
        'contract_number_format',
        'contract_number_length',
        'contract_number_start',
        'contract_number_current',
        'billboard_code_prefix',
        'billboard_code_suffix',
        'billboard_code_format',
        'billboard_code_length',
        'billboard_code_start',
        'billboard_code_current',
        'timezone',
        'currency',
        'date_format',
        'time_format',
        'payment_terms_days',
        'late_fee_percentage',
        'auto_generate_invoices',
        'tax_id',
        'default_tax_rate',
        'notification_settings',
        'company_description',
        'social_media_links',
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'auto_generate_invoices' => 'boolean',
        'late_fee_percentage' => 'decimal:2',
        'default_tax_rate' => 'decimal:2',
        'contract_number_length' => 'integer',
        'contract_number_start' => 'integer',
        'contract_number_current' => 'integer',
        'billboard_code_length' => 'integer',
        'billboard_code_start' => 'integer',
        'billboard_code_current' => 'integer',
        'payment_terms_days' => 'integer',
        'notification_settings' => 'array',
        'social_media_links' => 'array',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_owner', 'joined_at', 'role')
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

    /**
     * Get the company logo URL using Spatie Media Library
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('company_logo');
    }

    /**
     * Check if company has a logo
     */
    public function hasLogo(): bool
    {
        return $this->hasMedia('company_logo');
    }

    /**
     * Register media collections for the company
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('company_logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml']);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function contractTemplates(): HasMany
    {
        return $this->hasMany(ContractTemplate::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CompanySubscription::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CompanyTransaction::class);
    }

    public function billingAudits(): HasMany
    {
        return $this->hasMany(CompanyBillingAudit::class);
    }

    public function scopeWithActiveContracts($query)
    {
        return $query->whereHas('contracts', function ($q) {
            $q->where('status', 'active');
        });
    }

    /**
     * Get the ID of the company owner
     *
     * @return int|null
     */
    public function getOwnerIdAttribute()
    {
        $owner = $this->users()->wherePivot('is_owner', true)->first();

        return $owner ? $owner->id : null;
    }

    /**
     * Get billboards with converted prices in specified currency
     */
    public function getBillboardsInCurrency(?string $currency = null): \Illuminate\Database\Eloquent\Collection
    {
        $settingsService = app(\App\Services\CompanySettingsService::class);
        $targetCurrency = $currency ?? $this->currency ?? 'USD';

        return $settingsService->getBillboardPricesInCurrency($this, $targetCurrency);
    }

    /**
     * Initialize company settings with defaults
     */
    public function initializeSettings(): void
    {
        $settingsService = app(\App\Services\CompanySettingsService::class);
        $settingsService->initializeCompanySettings($this);
    }

    /**
     * Get next contract number preview
     */
    public function getNextContractNumberPreview(): string
    {
        $prefix = $this->contract_number_prefix ?? '';
        $suffix = $this->contract_number_suffix ?? '';
        $format = $this->contract_number_format ?? 'sequential';
        $length = $this->contract_number_length ?? 6;
        $nextNumber = ($this->contract_number_current ?? 0) + 1;

        $formattedNumber = $this->formatNumberPreview($nextNumber, $format, $length);

        return "{$prefix}{$formattedNumber}{$suffix}";
    }

    /**
     * Get next billboard code preview
     */
    public function getNextBillboardCodePreview(): string
    {
        $prefix = $this->billboard_code_prefix ?? '';
        $suffix = $this->billboard_code_suffix ?? '';
        $format = $this->billboard_code_format ?? 'sequential';
        $length = $this->billboard_code_length ?? 4;
        $nextNumber = ($this->billboard_code_current ?? 0) + 1;

        $formattedCode = $this->formatNumberPreview($nextNumber, $format, $length);

        return "{$prefix}{$formattedCode}{$suffix}";
    }

    protected static function booted(): void
    {
        self::creating(function (Company $company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name);
            }
        });
    }

    /**
     * Format a number preview (duplicated from service for preview purposes)
     */
    private function formatNumberPreview(int $number, string $format, int $length): string
    {
        return match ($format) {
            'sequential' => mb_str_pad("$number", $length, '0', STR_PAD_LEFT),
            'date_based' => $this->formatDateBasedNumberPreview($number, $length),
            'location_based' => mb_str_pad("$number", $length, '0', STR_PAD_LEFT),
            'custom' => mb_str_pad("$number", $length, '0', STR_PAD_LEFT),
            default => mb_str_pad("$number", $length, '0', STR_PAD_LEFT),
        };
    }

    /**
     * Format a date-based number preview
     */
    private function formatDateBasedNumberPreview(int $number, int $length): string
    {
        $now = now();
        $yearMonth = $now->format('Ym');
        $remainingLength = max(1, $length - 6);
        $paddedNumber = mb_str_pad($number, $remainingLength, '0', STR_PAD_LEFT);

        return $yearMonth.$paddedNumber;
    }
}
