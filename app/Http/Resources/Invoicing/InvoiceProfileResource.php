<?php

declare(strict_types=1);

namespace App\Http\Resources\Invoicing;

use App\Http\Resources\Clients\ClientResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property \App\Domains\Invoicing\Enums\InvoiceProfileFrequency $frequency
 * @property \Carbon\Carbon $next_issue_date
 * @property \Carbon\Carbon|null $end_date
 * @property int|null $occurrences_remaining
 * @property bool $auto_send
 * @property bool $is_active
 * @property array $template_data
 * @property \Carbon\Carbon|null $last_generated_at
 */
class InvoiceProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'frequency' => $this->frequency->value,
            'next_issue_date' => $this->next_issue_date->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'occurrences_remaining' => $this->occurrences_remaining,
            'auto_send' => $this->auto_send,
            'is_active' => $this->is_active,
            'is_paused' => ! $this->is_active,
            'template_data' => $this->template_data,
            'client' => new ClientResource($this->whenLoaded('client')),
            'last_generated_at' => $this->last_generated_at?->toDateString(),
        ];
    }
}
