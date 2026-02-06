<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Exceptions;

use App\Exceptions\DomainException;

class InvoiceCannotBeEditedException extends DomainException
{
    public static function forStatus(string $status): self
    {
        return new self("Invoice cannot be edited while status is [{$status}].");
    }
}
