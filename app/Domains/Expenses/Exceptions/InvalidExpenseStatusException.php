<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Exceptions;

use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Exceptions\DomainException;

class InvalidExpenseStatusException extends DomainException
{
    public function __construct(ExpenseStatus $from, ExpenseStatus $to)
    {
        parent::__construct("Cannot transition expense status from [{$from->value}] to [{$to->value}].");
    }
}
