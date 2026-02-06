<?php

declare(strict_types=1);

namespace App\Http\Resources\Payments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domains\Payments\Models\Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => number_format(((int) $this->amount) / 100, 2, '.', ''),
            'currency' => $this->currency_code,
            'method' => $this->method?->value,
            'method_label' => $this->method?->label(),
            'status' => $this->status?->value,
            'gateway' => $this->gateway?->value,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'invoice' => [
                'id' => $this->invoice?->id,
                'invoice_number' => $this->invoice?->invoice_number,
                'amount_outstanding' => $this->invoice !== null
                    ? number_format(((int) $this->invoice->amount_outstanding) / 100, 2, '.', '')
                    : null,
            ],
            'client' => [
                'id' => $this->client?->id,
                'name' => trim(((string) $this->client?->first_name).' '.((string) $this->client?->last_name)),
                'company_name' => $this->client?->company_name,
            ],
            'refunds' => PaymentRefundResource::collection($this->whenLoaded('refunds')),
            'can_be_refunded' => $this->isRefundable(),
        ];
    }
}
