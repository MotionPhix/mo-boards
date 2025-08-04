<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasedTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'template_id',
        'purchase_price',
        'purchased_at',
        'purchased_by',
        'purchase_metadata',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'purchased_at' => 'datetime',
        'purchase_metadata' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }
}
