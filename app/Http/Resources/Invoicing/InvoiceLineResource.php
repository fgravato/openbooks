<?php

declare(strict_types=1);

namespace App\Http\Resources\Invoicing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $type
 * @property string $description
 * @property float|string $quantity
 * @property int $unit_price
 * @property int $amount
 * @property string|null $tax_name
 * @property float|string|null $tax_percent
 * @property int $tax_amount
 * @property int|null $expense_id
 * @property int|null $time_entry_id
 * @property int $sort_order
 */
class InvoiceLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->description,
            'quantity' => (float) $this->quantity,
            'unit_price' => $this->unit_price,
            'unit_price_formatted' => number_format($this->unit_price / 100, 2),
            'amount' => $this->amount,
            'tax_name' => $this->tax_name,
            'tax_percent' => (float) $this->tax_percent,
            'tax_amount' => $this->getTaxAmount(),
            'expense_id' => $this->expense_id,
            'time_entry_id' => $this->time_entry_id,
            'sort_order' => $this->sort_order,
        ];
    }
}
