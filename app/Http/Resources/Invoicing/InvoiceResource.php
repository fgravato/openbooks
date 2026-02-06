<?php

declare(strict_types=1);

namespace App\Http\Resources\Invoicing;

use App\Http\Resources\Clients\ClientResource;
use App\Http\Resources\Payments\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $invoice_number
 * @property \App\Domains\Invoicing\Enums\InvoiceStatus $status
 * @property \Carbon\Carbon $issue_date
 * @property \Carbon\Carbon $due_date
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon|null $viewed_at
 * @property \Carbon\Carbon|null $paid_at
 * @property string $currency_code
 * @property int $subtotal
 * @property int $tax_amount
 * @property int $total
 * @property int $amount_paid
 * @property int $amount_outstanding
 * @property string|null $notes
 * @property string|null $terms
 * @property string|null $po_number
 * @property string $template
 * @property \Carbon\Carbon $created_at
 */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'issue_date' => $this->issue_date->toDateString(),
            'due_date' => $this->due_date->toDateString(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'viewed_at' => $this->viewed_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'currency_code' => $this->currency_code,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => 0, // Placeholder
            'total' => $this->total,
            'amount_paid' => $this->amount_paid,
            'amount_outstanding' => $this->amount_outstanding,
            'notes' => $this->notes,
            'terms' => $this->terms,
            'po_number' => $this->po_number,
            'template' => $this->template,
            'can_edit' => $this->canBeEdited(),
            'can_send' => $this->status->canTransitionTo(\App\Domains\Invoicing\Enums\InvoiceStatus::Sent),
            'is_overdue' => $this->isOverdue(),
            'client' => new ClientResource($this->whenLoaded('client')),
            'lines' => InvoiceLineResource::collection($this->whenLoaded('lines')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
