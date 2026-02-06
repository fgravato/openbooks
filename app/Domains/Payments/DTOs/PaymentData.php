<?php

declare(strict_types=1);

namespace App\Domains\Payments\DTOs;

use App\Domains\Payments\Enums\PaymentMethod;
use Illuminate\Http\Request;

readonly class PaymentData
{
    public function __construct(
        public int $invoiceId,
        public int $amount,
        public PaymentMethod $method,
        public ?string $paymentMethodId,
        public ?string $notes,
        public ?array $metadata,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            invoiceId: (int) $request->integer('invoice_id'),
            amount: (int) $request->integer('amount'),
            method: PaymentMethod::from((string) $request->input('method')),
            paymentMethodId: $request->filled('payment_method_id') ? (string) $request->input('payment_method_id') : null,
            notes: $request->filled('notes') ? (string) $request->input('notes') : null,
            metadata: $request->filled('metadata') ? (array) $request->input('metadata') : null,
        );
    }
}
