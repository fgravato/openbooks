<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoicing;

use Illuminate\Foundation\Http\FormRequest;

class SendInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('send', $this->route('invoice'));
    }

    public function rules(): array
    {
        return [
            'to' => ['required', 'email'],
            'cc' => ['nullable', 'array'],
            'cc.*' => ['email'],
            'bcc' => ['nullable', 'array'],
            'bcc.*' => ['email'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'attach_pdf' => ['boolean'],
        ];
    }
}
