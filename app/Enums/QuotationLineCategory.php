<?php

declare(strict_types=1);

namespace App\Enums;

enum QuotationLineCategory: string
{
    case Space = 'space';
    case Av = 'av';
    case Catering = 'catering';
    case Staffing = 'staffing';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Space => 'Venue / space',
            self::Av => 'Audio / visual',
            self::Catering => 'Catering',
            self::Staffing => 'Staffing',
            self::Other => 'Other',
        };
    }
}
