<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoicing;

use App\Domains\Invoicing\Enums\InvoiceLineType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateInvoiceLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('line')->invoice);
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'required', new Enum(InvoiceLineType::class)],
            'description' => ['sometimes', 'required', 'string'],
            'quantity' => ['sometimes', 'required', 'numeric', 'min:0'],
            'unit_price' => ['sometimes', 'required', 'integer'],
            'tax_name' => ['nullable', 'string'],
            'tax_percent' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['sometimes', 'required', 'integer'],
        ];
    }
}
