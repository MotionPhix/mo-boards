<?php

namespace App\Services\PayChangu;

class PayChanguPaymentRequest
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $reference,
    public readonly ?string $callbackUrl,
        public readonly string $returnUrl,
        public readonly string $cancelUrl,
        public readonly string $customerFirstName,
        public readonly string $customerLastName,
        public readonly string $customerEmail,
        public readonly ?string $customerPhone = null,
        public readonly array $metadata = []
    ) {}

    public static function create(array $data): self
    {
        return new self(
            amount: $data['amount'],
            currency: $data['currency'] ?? 'MWK',
            reference: $data['reference'],
            callbackUrl: $data['callback_url'] ?? null,
            returnUrl: $data['return_url'],
            cancelUrl: $data['cancel_url'],
            customerFirstName: $data['customer_first_name'],
            customerLastName: $data['customer_last_name'],
            customerEmail: $data['customer_email'],
            customerPhone: $data['customer_phone'] ?? null,
            metadata: $data['metadata'] ?? []
        );
    }
}
