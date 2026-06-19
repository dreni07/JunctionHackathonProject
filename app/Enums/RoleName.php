<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The three platform roles. The string value is the machine name stored in
 * the `roles.name` column; the label() is shown to humans.
 */
enum RoleName: string
{
    case Organizer = 'organizer';
    case Operations = 'operations';
    case Management = 'management';

    public function label(): string
    {
        return match ($this) {
            self::Organizer => 'External Organizer',
            self::Operations => 'Pyramid Operations Team',
            self::Management => 'Management',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Organizer => 'Submits and tracks their own event requests.',
            self::Operations => 'Runs day-to-day event operations: spaces, inventory, reservations, tasks.',
            self::Management => 'Oversees operations, approves proposals, and manages users.',
        };
    }
}
