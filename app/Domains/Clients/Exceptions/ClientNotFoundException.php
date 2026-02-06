<?php

declare(strict_types=1);

namespace App\Domains\Clients\Exceptions;

use App\Exceptions\DomainException;

class ClientNotFoundException extends DomainException
{
    public static function withId(int|string $clientId): self
    {
        return new self("Client [{$clientId}] was not found.");
    }
}
