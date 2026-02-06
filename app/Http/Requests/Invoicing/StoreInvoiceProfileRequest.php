<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoicing;

use App\Domains\Invoicing\Enums\InvoiceProfileFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreInvoiceProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'frequency' => ['required', new Enum(InvoiceProfileFrequency::class)],
            'custom_days' => ['required_if:frequency,custom', 'nullable', 'integer', 'min:1'],
            'next_issue_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:next_issue_date'],
            'occurrences_remaining' => ['nullable', 'integer', 'min:1'],
            'auto_send' => ['boolean'],
            'template_data' => ['required', 'array'],
            'template_data.currency_code' => ['required', 'string', 'size:3'],
            'template_data.lines' => ['required', 'array', 'min:1'],
        ];
    }
}
