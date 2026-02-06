<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Exceptions;

use App\Exceptions\DomainException;

class DuplicateInvoiceNumberException extends DomainException
{
    public function __construct(string $invoiceNumber, int $organizationId)
    {
        parent::__construct("Invoice number [{$invoiceNumber}] already exists for organization [{$organizationId}].");
    }
}
