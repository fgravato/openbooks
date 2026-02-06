<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoicing;

use App\Domains\Invoicing\Enums\DiscountType;
use App\Domains\Invoicing\Enums\InvoiceLineType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'currency_code' => ['required', 'string', 'size:3'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'po_number' => ['nullable', 'string'],
            'discount_type' => ['nullable', new Enum(DiscountType::class)],
            'discount_value' => ['nullable', 'integer', 'min:0'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.type' => ['required', new Enum(InvoiceLineType::class)],
            'lines.*.description' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0'],
            'lines.*.unit_price' => ['required', 'integer'],
            'lines.*.tax_name' => ['nullable', 'string'],
            'lines.*.tax_percent' => ['nullable', 'numeric', 'min:0'],
            'lines.*.expense_id' => ['nullable', 'exists:expenses,id'],
            'lines.*.time_entry_id' => ['nullable', 'exists:time_entries,id'],
        ];
    }
}
