<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Exceptions;

use App\Exceptions\DomainException;

class InvoiceNotFoundException extends DomainException
{
    public static function withId(int|string $invoiceId): self
    {
        return new self("Invoice [{$invoiceId}] was not found.");
    }
}
