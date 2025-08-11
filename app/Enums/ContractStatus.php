<?php

namespace App\Enums;

enum ContractStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Pending => 'Pending',
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Validation rule string for use in controllers/requests.
     */
    public static function validationRule(): string
    {
        return 'in:' . implode(',', array_map(fn($c) => $c->value, self::cases()));
    }
}
