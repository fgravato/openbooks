<?php

declare(strict_types=1);

namespace App\Domains\Payments\DTOs;

readonly class PaymentResult
{
    public function __construct(
        public bool $success,
        public ?string $transactionId = null,
        public ?string $errorMessage = null,
        public ?string $status = null,
    ) {}
}
