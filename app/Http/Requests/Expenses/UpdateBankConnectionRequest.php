<?php

declare(strict_types=1);

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('expenses.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'institution_name' => ['sometimes', 'required', 'string', 'max:255'],
            'account_mask' => ['sometimes', 'required', 'string', 'size:4'],
            'currency_code' => ['sometimes', 'required', 'string', 'size:3'],
            'is_active' => ['boolean'],
        ];
    }
}
