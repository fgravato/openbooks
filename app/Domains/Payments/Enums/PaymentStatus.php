<?php

declare(strict_types=1);

namespace App\Domains\Payments\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';
    case Cancelled = 'cancelled';

    public function isSuccessful(): bool
    {
        return in_array($this, [self::Completed, self::PartiallyRefunded, self::Refunded], true);
    }
}
