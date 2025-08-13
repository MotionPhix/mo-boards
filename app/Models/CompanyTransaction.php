<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'type', 'payment_id', 'reference', 'amount', 'currency', 'status', 'description', 'raw_response', 'meta', 'occurred_at'
    ];

    protected $casts = [
        'raw_response' => 'array',
        'meta' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
