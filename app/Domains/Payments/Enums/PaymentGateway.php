<?php

declare(strict_types=1);

namespace App\Domains\Payments\Enums;

enum PaymentGateway: string
{
    case Stripe = 'stripe';
    case PayPal = 'paypal';
    case Manual = 'manual';

    public function isAutomated(): bool
    {
        return in_array($this, [self::Stripe, self::PayPal], true);
    }
}
