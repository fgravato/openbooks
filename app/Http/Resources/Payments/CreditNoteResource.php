<?php

declare(strict_types=1);

namespace App\Http\Resources\Payments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domains\Payments\Models\CreditNote
 */
class CreditNoteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->credit_note_number,
            'amount' => number_format(((int) $this->amount) / 100, 2, '.', ''),
            'remaining_amount' => number_format(((int) $this->remaining_amount) / 100, 2, '.', ''),
            'reason' => $this->reason,
            'is_fully_applied' => $this->isFullyApplied(),
            'applications' => $this->whenLoaded(
                'appliedTo',
                fn (): array => $this->appliedTo->map(static fn ($application): array => [
                    'id' => $application->id,
                    'invoice_id' => $application->invoice_id,
                    'amount' => number_format(((int) $application->amount) / 100, 2, '.', ''),
                    'applied_at' => $application->applied_at?->toIso8601String(),
                ])->values()->all(),
            ),
        ];
    }
}
