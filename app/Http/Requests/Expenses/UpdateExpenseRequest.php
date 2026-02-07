<?php

declare(strict_types=1);

namespace App\Http\Requests\Expenses;

use App\Domains\Expenses\Enums\ExpenseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('expense'));
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'integer', 'exists:expense_categories,id'],
            'vendor' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['sometimes', 'integer', 'min:0'],
            'currency_code' => ['sometimes', 'string', 'size:3'],
            'tax_name' => ['nullable', 'string', 'max:255'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_amount' => ['sometimes', 'integer', 'min:0'],
            'date' => ['sometimes', 'date'],
            'is_billable' => ['sometimes', 'boolean'],
            'is_reimbursable' => ['sometimes', 'boolean'],
            'markup_percent' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'project_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', Rule::in(array_map(static fn (ExpenseStatus $status): string => $status->value, ExpenseStatus::cases()))],
        ];
    }
}
