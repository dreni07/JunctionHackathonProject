<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The kind of issue a worker is raising an alert about.
 */
enum AlertCategory: string
{
    case Maintenance = 'maintenance';
    case Safety = 'safety';
    case Equipment = 'equipment';
    case Cleanliness = 'cleanliness';
    case Security = 'security';
    case Staffing = 'staffing';
    case Scheduling = 'scheduling';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Maintenance => 'Maintenance',
            self::Safety => 'Safety',
            self::Equipment => 'Equipment',
            self::Cleanliness => 'Cleanliness',
            self::Security => 'Security',
            self::Staffing => 'Staffing',
            self::Scheduling => 'Scheduling',
            self::Other => 'Other',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case): array => ['value' => $case->value, 'label' => $case->label()],
            self::cases(),
        );
    }
}
