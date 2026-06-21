<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Partial = 'partial';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Partial => 'Partially paid',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
            self::Void => 'Void',
        };
    }
}
