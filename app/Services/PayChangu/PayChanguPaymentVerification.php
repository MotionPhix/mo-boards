<?php

namespace App\Services\PayChangu;

class PayChanguPaymentVerification
{
    public function __construct(
        public readonly string $payment_id,
        public readonly string $reference,
        public readonly string $status,
        public readonly float $amount,
        public readonly string $currency,
        public readonly array $customer,
        public readonly array $metadata,
        public readonly ?string $paid_at,
        public readonly string $created_at,
        public readonly string $updated_at
    ) {}

    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed' || $this->status === 'cancelled';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' || $this->status === 'processing';
    }

    public function toArray(): array
    {
        return [
            'payment_id' => $this->payment_id,
            'reference' => $this->reference,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'customer' => $this->customer,
            'metadata' => $this->metadata,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
