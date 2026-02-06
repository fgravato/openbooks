<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Enums;

enum RecurringExpenseFrequency: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annually = 'annually';
}
