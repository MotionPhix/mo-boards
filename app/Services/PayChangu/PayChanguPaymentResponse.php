<?php

namespace App\Services\PayChangu;

class PayChanguPaymentResponse
{
    public function __construct(
        public readonly string $payment_id,
        public readonly string $reference,
        public readonly string $checkout_url,
        public readonly string $status,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $created_at
    ) {}

    public function toArray(): array
    {
        return [
            'payment_id' => $this->payment_id,
            'reference' => $this->reference,
            'checkout_url' => $this->checkout_url,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'created_at' => $this->created_at,
        ];
    }
}
