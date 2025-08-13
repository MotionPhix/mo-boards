<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractPayment extends Model
{
  use HasFactory;

  protected $fillable = [
    'contract_id',
    'amount',
    'due_date',
    'paid_date',
    'status',
    'payment_method',
    'reference_number',
    'notes',
  ];

  protected $casts = [
    'amount' => 'decimal:2',
    'due_date' => 'date',
    'paid_date' => 'date',
  ];

  public function contract(): BelongsTo
  {
    return $this->belongsTo(Contract::class);
  }

  // Check if payment is overdue
  public function isOverdue(): bool
  {
    return $this->status === 'pending' && $this->due_date < now()->toDateString();
  }

  // Mark payment as paid
  public function markAsPaid(?string $paymentMethod = null, ?string $referenceNumber = null): void
  {
    $this->update([
      'status' => 'paid',
      'paid_date' => now(),
      'payment_method' => $paymentMethod,
      'reference_number' => $referenceNumber,
    ]);
  }

  // Scopes
  public function scopePending($query)
  {
    return $query->where('status', 'pending');
  }

  public function scopeOverdue($query)
  {
    return $query->where('status', 'pending')
      ->where('due_date', '<', now());
  }

  public function scopePaid($query)
  {
    return $query->where('status', 'paid');
  }
}
