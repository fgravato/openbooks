<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\DTOs;

use App\Domains\Invoicing\Enums\InvoiceProfileFrequency;
use DateTime;
use Illuminate\Http\Request;

readonly class InvoiceProfileData
{
    public function __construct(
        public int $clientId,
        public string $name,
        public InvoiceProfileFrequency $frequency,
        public ?int $customDays,
        public DateTime $nextIssueDate,
        public ?DateTime $endDate,
        public ?int $occurrencesRemaining,
        public bool $autoSend,
        public array $templateData,
        public bool $isActive,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            clientId: (int) $request->integer('client_id'),
            name: (string) $request->input('name'),
            frequency: InvoiceProfileFrequency::from((string) $request->input('frequency')),
            customDays: $request->filled('custom_days') ? (int) $request->integer('custom_days') : null,
            nextIssueDate: new DateTime((string) $request->input('next_issue_date', \now()->toDateString())),
            endDate: $request->filled('end_date') ? new DateTime((string) $request->input('end_date')) : null,
            occurrencesRemaining: $request->filled('occurrences_remaining')
                ? (int) $request->integer('occurrences_remaining')
                : null,
            autoSend: $request->boolean('auto_send'),
            templateData: (array) $request->input('template_data', []),
            isActive: $request->boolean('is_active', true),
        );
    }
}
