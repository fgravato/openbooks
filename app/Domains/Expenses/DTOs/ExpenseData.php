<?php

declare(strict_types=1);

namespace App\Domains\Expenses\DTOs;

use DateTime;
use Illuminate\Http\Request;

readonly class ExpenseData
{
    public function __construct(
        public int $categoryId,
        public string $vendor,
        public string $description,
        public int $amount,
        public DateTime $date,
        public bool $isBillable,
        public bool $isReimbursable,
        public ?float $markupPercent,
        public ?int $clientId,
        public ?int $projectId,
        public ?string $notes,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            categoryId: (int) $request->integer('category_id'),
            vendor: (string) $request->input('vendor'),
            description: (string) $request->input('description'),
            amount: (int) $request->integer('amount'),
            date: new DateTime((string) $request->input('date')),
            isBillable: (bool) $request->boolean('is_billable'),
            isReimbursable: (bool) $request->boolean('is_reimbursable'),
            markupPercent: $request->filled('markup_percent') ? (float) $request->input('markup_percent') : null,
            clientId: $request->filled('client_id') ? (int) $request->integer('client_id') : null,
            projectId: $request->filled('project_id') ? (int) $request->integer('project_id') : null,
            notes: $request->filled('notes') ? (string) $request->input('notes') : null,
        );
    }
}
