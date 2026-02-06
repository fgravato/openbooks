<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Enums;

enum BankAccountType: string
{
    case Checking = 'checking';
    case Savings = 'savings';
    case Credit = 'credit';
}
