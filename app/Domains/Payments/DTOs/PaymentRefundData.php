<?php

declare(strict_types=1);

namespace App\Domains\Payments\DTOs;

readonly class PaymentRefundData
{
    public function __construct(
        public int $paymentId,
        public int $amount,
        public string $reason,
    ) {}
}
