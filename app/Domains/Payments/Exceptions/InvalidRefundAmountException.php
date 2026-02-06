<?php

declare(strict_types=1);

namespace App\Domains\Payments\Exceptions;

use App\Exceptions\DomainException;

class InvalidRefundAmountException extends DomainException
{
}
