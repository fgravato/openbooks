<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Exceptions;

use App\Exceptions\DomainException;

class ExpenseNotFoundException extends DomainException
{
    public static function withId(int|string $expenseId): self
    {
        return new self("Expense [{$expenseId}] was not found.");
    }
}
