<?php

declare(strict_types=1);

namespace App\Domains\Payments\Exceptions;

use App\Exceptions\DomainException;

class GatewayException extends DomainException
{
    public function __construct(string $gateway, string $errorMessage)
    {
        parent::__construct("{$gateway} gateway error: {$errorMessage}");
    }
}
