<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The kind of physical asset tracked via QR codes.
 */
enum AssetType: string
{
    case Chair = 'chair';
    case Table = 'table';
    case Microphone = 'microphone';
    case Projector = 'projector';
    case Camera = 'camera';
    case Screen = 'screen';
    case Speaker = 'speaker';

    public function label(): string
    {
        return match ($this) {
            self::Chair => 'Chair',
            self::Table => 'Table',
            self::Microphone => 'Microphone',
            self::Projector => 'Projector',
            self::Camera => 'Camera',
            self::Screen => 'Screen',
            self::Speaker => 'Speaker',
        };
    }
}
