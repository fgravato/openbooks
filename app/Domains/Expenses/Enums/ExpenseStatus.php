<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Enums;

enum ExpenseStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Reimbursed = 'reimbursed';
    case Billed = 'billed';

    public function canTransitionTo(ExpenseStatus $newStatus): bool
    {
        return in_array($newStatus, match ($this) {
            self::Pending => [self::Approved, self::Rejected],
            self::Approved => [self::Rejected, self::Reimbursed, self::Billed],
            self::Rejected => [],
            self::Reimbursed => [self::Billed],
            self::Billed => [],
        }, true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Reimbursed => 'Reimbursed',
            self::Billed => 'Billed',
        };
    }
}
