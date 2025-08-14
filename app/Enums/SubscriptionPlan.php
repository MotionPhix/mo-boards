<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionPlan: string
{
    case FREE = 'free';
    case PRO = 'pro';
    case BUSINESS = 'business';

    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }

    public static function options(): array
    {
        return array_map(static fn (self $c) => [
            'value' => $c->value,
            'label' => $c->label(),
        ], self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Free',
            self::PRO => 'Pro',
            self::BUSINESS => 'Business',
        };
    }
}
