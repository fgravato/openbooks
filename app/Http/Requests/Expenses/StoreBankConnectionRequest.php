<?php

declare(strict_types=1);

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'public_token' => ['required', 'string'],
            'account_id' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
