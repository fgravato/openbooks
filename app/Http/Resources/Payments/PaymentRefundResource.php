<?php

declare(strict_types=1);

namespace App\Http\Resources\Payments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domains\Payments\Models\PaymentRefund
 */
class PaymentRefundResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => number_format(((int) $this->amount) / 100, 2, '.', ''),
            'reason' => $this->reason,
            'refunded_at' => $this->refunded_at?->toIso8601String(),
            'refunded_by' => [
                'id' => $this->refundedBy?->id,
                'name' => $this->refundedBy?->name,
            ],
        ];
    }
}
