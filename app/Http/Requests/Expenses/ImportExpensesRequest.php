<?php

declare(strict_types=1);

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class ImportExpensesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('expenses.manage');
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt,ofx'],
            'category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'mappings' => ['nullable', 'array'],
            'mappings.vendor' => ['nullable', 'string'],
            'mappings.amount' => ['nullable', 'string'],
            'mappings.date' => ['nullable', 'string'],
            'mappings.description' => ['nullable', 'string'],
            'mappings.currency' => ['nullable', 'string'],
        ];
    }
}
