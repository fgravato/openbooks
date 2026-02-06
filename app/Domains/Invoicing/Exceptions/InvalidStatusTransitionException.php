<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Exceptions;

use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Exceptions\DomainException;

class InvalidStatusTransitionException extends DomainException
{
    public function __construct(InvoiceStatus $from, InvoiceStatus $to)
    {
        parent::__construct("Cannot transition invoice status from [{$from->value}] to [{$to->value}].");
    }
}
