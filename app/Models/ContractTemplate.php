<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuid;

class ContractTemplate extends Model
{
  use HasFactory, HasUuid;

  protected $fillable = [
    'company_id',
    'name',
    'description',
    'content',
    'default_terms',
    'custom_fields',
    'is_active',
    'is_system_template',
    'price',
    'category',
    'features',
    'preview_image',
    'tags',
  ];

  protected $casts = [
    'default_terms' => 'array',
    'custom_fields' => 'array',
    'is_active' => 'boolean',
    'is_system_template' => 'boolean',
    'price' => 'decimal:2',
    'tags' => 'array',
  ];

  public function company(): BelongsTo
  {
    return $this->belongsTo(Company::class);
  }

  public function contracts(): HasMany
  {
    return $this->hasMany(Contract::class, 'template_id');
  }

  public function purchasedBy(): HasMany
  {
    return $this->hasMany(PurchasedTemplate::class, 'template_id');
  }

  // Scope for active templates
  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  // Scope for system templates
  public function scopeSystemTemplates($query)
  {
    return $query->where('is_system_template', true);
  }

  // Scope for company templates
  public function scopeCompanyTemplates($query)
  {
    return $query->where('is_system_template', false);
  }

  // Scope for templates available to a specific company
  public function scopeAvailableToCompany($query, $companyId)
  {
    return $query->where(function ($q) use ($companyId) {
      // Company's own templates
      $q->where('company_id', $companyId)
        // OR system templates that are purchased by the company
        ->orWhereHas('purchasedBy', function ($purchased) use ($companyId) {
          $purchased->where('company_id', $companyId);
        });
    });
  }

  // Check if template is purchased by a company
  public function isPurchasedBy($companyId): bool
  {
    if (!$this->is_system_template) {
      return $this->company_id === $companyId;
    }

    return $this->purchasedBy()
      ->where('company_id', $companyId)
      ->exists();
  }

  // Check if template is a system template
  public function isSystem(): bool
  {
    return (bool) $this->is_system_template;
  }

  // Get preview content (limited for unpurchased system templates)
  public function getPreviewContent($companyId = null): string
  {
    if (!$this->is_system_template || ($companyId && $this->isPurchasedBy($companyId))) {
      return $this->content ?? '';
    }

    // Return limited preview for unpurchased system templates
    $content = $this->content ?? '';
    $words = explode(' ', strip_tags($content));

    // Return first 100 words as preview
    return implode(' ', array_slice($words, 0, 100)) .
      (count($words) > 100 ? '... [Preview limited - Purchase to unlock full template]' : '');
  }
}
