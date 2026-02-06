<?php

declare(strict_types=1);

namespace App\Domains\Identity\Exceptions;

use App\Exceptions\DomainException;

class UnauthorizedException extends DomainException
{
    public static function forPermission(string $permission): self
    {
        return new self("Missing required permission [{$permission}].");
    }

    public static function forOrganization(): self
    {
        return new self('The authenticated user cannot access this organization.');
    }
}
