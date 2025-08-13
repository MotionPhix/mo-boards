<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'name', 'price', 'currency', 'interval', 'interval_count', 'features', 'active'
    ];

    protected $casts = [
        'features' => 'array',
        'active' => 'boolean',
    ];
}
