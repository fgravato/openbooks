<?php

declare(strict_types=1);

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class ImportExpensesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt'],
            'category_id' => ['required', 'exists:expense_categories,id'],
            'mappings' => ['required', 'array'],
            'mappings.vendor' => ['required', 'string'],
            'mappings.amount' => ['required', 'string'],
            'mappings.date' => ['required', 'string'],
            'mappings.description' => ['nullable', 'string'],
        ];
    }
}
