<?php

declare(strict_types=1);

namespace App\Enums;

enum BillboardStatus: string
{
    case ACTIVE = 'active';
    case MAINTENANCE = 'maintenance';
    case REMOVED = 'removed';
    case AVAILABLE = 'available';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::MAINTENANCE => 'Maintenance',
            self::REMOVED => 'Removed',
            self::AVAILABLE => 'Available',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }

    public static function options(): array
    {
        return array_map(fn(self $c) => [
            'value' => $c->value,
            'label' => $c->label(),
        ], self::cases());
    }
}
