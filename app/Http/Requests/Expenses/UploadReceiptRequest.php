<?php

declare(strict_types=1);

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class UploadReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('expense'));
    }

    public function rules(): array
    {
        return [
            'receipt' => ['nullable', 'required_without:file', 'file', 'mimes:jpeg,png,jpg,pdf,webp', 'max:10240'],
            'file' => ['nullable', 'required_without:receipt', 'file', 'mimes:jpeg,png,jpg,pdf,webp', 'max:10240'],
        ];
    }
}
