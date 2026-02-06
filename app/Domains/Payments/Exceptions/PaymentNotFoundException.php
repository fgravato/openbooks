<?php

declare(strict_types=1);

namespace App\Domains\Payments\Exceptions;

use App\Exceptions\DomainException;

class PaymentNotFoundException extends DomainException
{
    public static function withId(int|string $paymentId): self
    {
        return new self("Payment [{$paymentId}] was not found.");
    }
}
