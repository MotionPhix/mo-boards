<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractTemplate extends Model
{
  use HasFactory;

  protected $fillable = [
    'company_id',
    'name',
    'description',
    'default_terms',
    'custom_fields',
    'is_active',
  ];

  protected $casts = [
    'default_terms' => 'array',
    'custom_fields' => 'array',
    'is_active' => 'boolean',
  ];

  public function company(): BelongsTo
  {
    return $this->belongsTo(Company::class);
  }

  public function contracts(): HasMany
  {
    return $this->hasMany(Contract::class, 'template_id');
  }

  // Scope for active templates
  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }
}
