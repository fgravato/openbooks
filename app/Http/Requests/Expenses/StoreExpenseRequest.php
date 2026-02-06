<?php

declare(strict_types=1);

namespace App\Http\Requests\Expenses;

use App\Domains\Expenses\Enums\ExpenseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('expenses.manage')
            || (bool) $this->user()?->hasPermission('expenses.create');
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'vendor' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'amount' => ['required', 'integer', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'tax_name' => ['nullable', 'string', 'max:255'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'date' => ['required', 'date'],
            'is_billable' => ['required', 'boolean'],
            'is_reimbursable' => ['required', 'boolean'],
            'markup_percent' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'project_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::in(array_map(static fn (ExpenseStatus $status): string => $status->value, ExpenseStatus::cases()))],
        ];
    }
}
