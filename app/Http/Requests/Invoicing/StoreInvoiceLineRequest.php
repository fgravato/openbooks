<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoicing;

use App\Domains\Invoicing\Enums\InvoiceLineType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreInvoiceLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('invoice'));
    }

    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(InvoiceLineType::class)],
            'description' => ['required', 'string'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'integer'],
            'tax_name' => ['nullable', 'string'],
            'tax_percent' => ['nullable', 'numeric', 'min:0'],
            'expense_id' => ['nullable', 'exists:expenses,id'],
            'time_entry_id' => ['nullable', 'exists:time_entries,id'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
