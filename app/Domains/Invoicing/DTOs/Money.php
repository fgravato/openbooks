<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\DTOs;

readonly class Money
{
    public function __construct(
        public int $cents,
        public string $currency,
    ) {
    }
}
