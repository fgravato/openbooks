<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Enums;

enum InvoiceLineType: string
{
    case Item = 'item';
    case Time = 'time';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Item => 'Item',
            self::Time => 'Time',
            self::Expense => 'Expense',
        };
    }
}
