<?php

declare(strict_types=1);

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('payments.manage');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string'],
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
        ];
    }
}
