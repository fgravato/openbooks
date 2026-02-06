<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Viewed = 'viewed';
    case Partial = 'partial';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function canTransitionTo(InvoiceStatus $newStatus): bool
    {
        return in_array($newStatus, match ($this) {
            self::Draft => [self::Sent, self::Cancelled],
            self::Sent => [self::Viewed, self::Partial, self::Paid, self::Overdue, self::Cancelled],
            self::Viewed => [self::Partial, self::Paid, self::Overdue, self::Cancelled],
            self::Partial => [self::Paid, self::Overdue, self::Cancelled],
            self::Overdue => [self::Partial, self::Paid, self::Cancelled],
            self::Paid => [],
            self::Cancelled => [],
        }, true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Viewed => 'Viewed',
            self::Partial => 'Partially Paid',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'text-slate-600 bg-slate-100',
            self::Sent => 'text-blue-700 bg-blue-100',
            self::Viewed => 'text-indigo-700 bg-indigo-100',
            self::Partial => 'text-amber-700 bg-amber-100',
            self::Paid => 'text-emerald-700 bg-emerald-100',
            self::Overdue => 'text-rose-700 bg-rose-100',
            self::Cancelled => 'text-zinc-700 bg-zinc-200',
        };
    }
}
