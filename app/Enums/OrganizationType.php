<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The kind of organization an event request comes from.
 */
enum OrganizationType: string
{
    case University = 'university';
    case Company = 'company';
    case Ngo = 'ngo';
    case Government = 'government';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::University => 'University',
            self::Company => 'Company',
            self::Ngo => 'NGO',
            self::Government => 'Government',
            self::Other => 'Other',
        };
    }
}
