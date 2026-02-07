<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Enums;

enum DiscountType: string
{
    case Percentage = 'percentage';
    case FixedAmount = 'fixed_amount';

    public function calculateDiscount(float $subtotal, float $value): float
    {
        if ($subtotal <= 0.0 || $value <= 0.0) {
            return 0.0;
        }

        return match ($this) {
            self::Percentage => min($subtotal, ($subtotal * $value) / 10000), // value in basis points (1000 = 10%)
            self::FixedAmount => min($subtotal, $value),
        };
    }
}
