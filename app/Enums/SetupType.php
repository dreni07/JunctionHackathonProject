<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * How a space is physically laid out for an event — drives asset needs
 * (chairs, tables) and space matching.
 */
enum SetupType: string
{
    case Theater = 'theater';
    case Classroom = 'classroom';
    case Mixed = 'mixed';
    case Exhibition = 'exhibition';

    public function label(): string
    {
        return match ($this) {
            self::Theater => 'Theater',
            self::Classroom => 'Classroom',
            self::Mixed => 'Mixed',
            self::Exhibition => 'Exhibition',
        };
    }
}
