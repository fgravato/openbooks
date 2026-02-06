<?php

declare(strict_types=1);

namespace App\Domains\Payments\DTOs;

readonly class CreditNoteData
{
    public function __construct(
        public int $clientId,
        public int $amount,
        public string $reason,
        public ?int $invoiceId,
    ) {
    }
}
