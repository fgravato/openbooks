<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\DTOs;

use App\Domains\Invoicing\Enums\DiscountType;
use DateTime;
use Illuminate\Http\Request;

readonly class InvoiceData
{
    public function __construct(
        public int $clientId,
        public DateTime $issueDate,
        public DateTime $dueDate,
        public ?string $notes,
        public ?string $terms,
        public array $lines,
        public ?DiscountType $discountType,
        public ?float $discountValue,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $lineData = array_map(
            static fn (array $line): InvoiceLineData => InvoiceLineData::fromArray($line),
            (array) $request->input('lines', []),
        );

        return new self(
            clientId: (int) $request->integer('client_id'),
            issueDate: new DateTime((string) $request->input('issue_date', \now()->toDateString())),
            dueDate: new DateTime((string) $request->input('due_date', \now()->toDateString())),
            notes: $request->filled('notes') ? (string) $request->input('notes') : null,
            terms: $request->filled('terms') ? (string) $request->input('terms') : null,
            lines: $lineData,
            discountType: $request->filled('discount_type')
                ? DiscountType::from((string) $request->input('discount_type'))
                : null,
            discountValue: $request->filled('discount_value')
                ? (float) $request->input('discount_value')
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'issue_date' => $this->issueDate->format('Y-m-d'),
            'due_date' => $this->dueDate->format('Y-m-d'),
            'notes' => $this->notes,
            'terms' => $this->terms,
            'lines' => array_map(
                static fn (InvoiceLineData $line): array => [
                    'type' => $line->type->value,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unitPrice,
                    'tax_name' => $line->taxName,
                    'tax_percent' => $line->taxPercent,
                    'expense_id' => $line->expenseId,
                    'time_entry_id' => $line->timeEntryId,
                    'amount' => $line->calculateAmount(),
                ],
                $this->lines,
            ),
            'discount_type' => $this->discountType?->value,
            'discount_value' => $this->discountValue,
        ];
    }
}
