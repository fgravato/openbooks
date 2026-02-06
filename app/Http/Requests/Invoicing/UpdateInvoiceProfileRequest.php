<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoicing;

use App\Domains\Invoicing\Enums\InvoiceProfileFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateInvoiceProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('profile'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'frequency' => ['sometimes', 'required', new Enum(InvoiceProfileFrequency::class)],
            'custom_days' => ['required_if:frequency,custom', 'nullable', 'integer', 'min:1'],
            'next_issue_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['nullable', 'date', 'after:next_issue_date'],
            'occurrences_remaining' => ['nullable', 'integer', 'min:1'],
            'auto_send' => ['boolean'],
            'is_active' => ['boolean'],
            'template_data' => ['sometimes', 'required', 'array'],
        ];
    }
}
