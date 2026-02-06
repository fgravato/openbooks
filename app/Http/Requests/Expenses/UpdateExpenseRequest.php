<?php

declare(strict_types=1);

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('expense'));
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'required', 'exists:expense_categories,id'],
            'vendor' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['sometimes', 'required', 'integer', 'min:0'],
            'date' => ['sometimes', 'required', 'date'],
            'is_billable' => ['boolean'],
            'is_reimbursable' => ['boolean'],
            'markup_percent' => ['nullable', 'numeric', 'min:0'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
