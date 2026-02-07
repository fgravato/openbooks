<?php

declare(strict_types=1);

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('expenses.manage');
    }

    public function rules(): array
    {
        return [
            'public_token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'institution_name' => ['required', 'string', 'max:255'],
            'institution_id' => ['required', 'string', 'max:255'],
            'account_mask' => ['required', 'string', 'size:4'],
            'account_type' => ['required', 'string', 'in:checking,savings,credit'],
            'currency_code' => ['nullable', 'string', 'size:3'],
        ];
    }
}
