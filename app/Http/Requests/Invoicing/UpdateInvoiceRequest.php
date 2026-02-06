<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoicing;

use App\Domains\Invoicing\Enums\DiscountType;
use App\Domains\Invoicing\Enums\InvoiceLineType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('invoice'));
    }

    public function rules(): array
    {
        return [
            'client_id' => ['sometimes', 'required', 'exists:clients,id'],
            'issue_date' => ['sometimes', 'required', 'date'],
            'due_date' => ['sometimes', 'required', 'date', 'after_or_equal:issue_date'],
            'currency_code' => ['sometimes', 'required', 'string', 'size:3'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'po_number' => ['nullable', 'string'],
            'discount_type' => ['nullable', new Enum(DiscountType::class)],
            'discount_value' => ['nullable', 'integer', 'min:0'],
            'lines' => ['sometimes', 'array'],
            'lines.*.id' => ['nullable', 'exists:invoice_lines,id'],
            'lines.*.type' => ['required_with:lines', new Enum(InvoiceLineType::class)],
            'lines.*.description' => ['required_with:lines', 'string'],
            'lines.*.quantity' => ['required_with:lines', 'numeric', 'min:0'],
            'lines.*.unit_price' => ['required_with:lines', 'integer'],
            'lines.*.tax_name' => ['nullable', 'string'],
            'lines.*.tax_percent' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
