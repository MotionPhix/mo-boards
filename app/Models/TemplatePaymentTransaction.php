<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplatePaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'template_id',
        'user_id',
        'payment_id',
        'reference',
        'amount',
        'currency',
        'status',
        'checkout_url',
        'return_url',
        'cancel_url',
        'customer_details',
        'metadata',
        'payment_initiated_at',
        'payment_completed_at',
        'payment_failed_at',
        'failure_reason',
        'paychangu_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'customer_details' => 'array',
        'metadata' => 'array',
        'paychangu_response' => 'array',
        'payment_initiated_at' => 'datetime',
        'payment_completed_at' => 'datetime',
        'payment_failed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['paid', 'completed']);
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'cancelled']);
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(array $payChanguResponse = []): void
    {
        $this->update([
            'status' => 'completed',
            'payment_completed_at' => now(),
            'paychangu_response' => $payChanguResponse,
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(string $reason = null, array $payChanguResponse = []): void
    {
        $this->update([
            'status' => 'failed',
            'payment_failed_at' => now(),
            'failure_reason' => $reason,
            'paychangu_response' => $payChanguResponse,
        ]);
    }

    /**
     * Generate unique reference for payment
     */
    public static function generateReference(): string
    {
        return 'TMPL_' . strtoupper(uniqid()) . '_' . time();
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['paid', 'completed']);
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'cancelled']);
    }
}
