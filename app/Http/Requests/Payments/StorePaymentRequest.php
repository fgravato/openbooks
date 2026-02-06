<?php

declare(strict_types=1);

namespace App\Http\Requests\Payments;

use App\Domains\Payments\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('payments.manage');
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        $onlineMethods = array_map(
            static fn (PaymentMethod $method): string => $method->value,
            array_filter(PaymentMethod::cases(), static fn (PaymentMethod $method): bool => $method->isOnline()),
        );

        return [
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', 'string', Rule::in(array_map(static fn (PaymentMethod $method): string => $method->value, PaymentMethod::cases()))],
            'payment_method_id' => ['nullable', 'string', Rule::requiredIf(fn (): bool => in_array((string) $this->input('method'), $onlineMethods, true))],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'reference_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}
