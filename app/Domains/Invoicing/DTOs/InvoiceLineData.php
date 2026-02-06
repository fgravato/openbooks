<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\DTOs;

use App\Domains\Invoicing\Enums\InvoiceLineType;

readonly class InvoiceLineData
{
    public function __construct(
        public InvoiceLineType $type,
        public string $description,
        public float $quantity,
        public int $unitPrice,
        public ?string $taxName,
        public ?float $taxPercent,
        public ?int $expenseId,
        public ?int $timeEntryId,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: InvoiceLineType::from((string) ($data['type'] ?? InvoiceLineType::Item->value)),
            description: (string) ($data['description'] ?? ''),
            quantity: (float) ($data['quantity'] ?? 1),
            unitPrice: (int) ($data['unit_price'] ?? 0),
            taxName: isset($data['tax_name']) ? (string) $data['tax_name'] : null,
            taxPercent: isset($data['tax_percent']) ? (float) $data['tax_percent'] : null,
            expenseId: isset($data['expense_id']) ? (int) $data['expense_id'] : null,
            timeEntryId: isset($data['time_entry_id']) ? (int) $data['time_entry_id'] : null,
        );
    }

    public function calculateAmount(): int
    {
        return (int) round($this->quantity * $this->unitPrice);
    }
}
