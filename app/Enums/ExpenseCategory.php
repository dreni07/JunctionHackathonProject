<?php

declare(strict_types=1);

namespace App\Enums;

enum ExpenseCategory: string
{
    case Staffing = 'staffing';
    case Utilities = 'utilities';
    case Maintenance = 'maintenance';
    case Marketing = 'marketing';
    case Supplies = 'supplies';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Staffing => 'Staffing',
            self::Utilities => 'Utilities',
            self::Maintenance => 'Maintenance',
            self::Marketing => 'Marketing',
            self::Supplies => 'Supplies',
            self::Other => 'Other',
        };
    }
}
