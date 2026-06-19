<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The top-level distinction between the two ways a person signs in:
 * an external Organization, or a tenant-based Operational worker.
 */
enum AccountType: string
{
    case Organization = 'organization';
    case Operational = 'operational';

    public function label(): string
    {
        return match ($this) {
            self::Organization => 'Organization',
            self::Operational => 'Operational Worker',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Organization => 'Plan and submit events as an external organization.',
            self::Operational => 'Sign into a Pyramid branch as a tenant-based worker.',
        };
    }
}
