<?php

declare(strict_types=1);

namespace App\Domains\Payments\Enums;

enum PaymentMethod: string
{
    case CreditCard = 'credit_card';
    case Ach = 'ach';
    case ApplePay = 'apple_pay';
    case GooglePay = 'google_pay';
    case PayPal = 'paypal';
    case Check = 'check';
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Other = 'other';

    public function isOnline(): bool
    {
        return in_array($this, [
            self::CreditCard,
            self::Ach,
            self::ApplePay,
            self::GooglePay,
            self::PayPal,
        ], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::CreditCard => 'Credit Card',
            self::Ach => 'ACH',
            self::ApplePay => 'Apple Pay',
            self::GooglePay => 'Google Pay',
            self::PayPal => 'PayPal',
            self::Check => 'Check',
            self::Cash => 'Cash',
            self::BankTransfer => 'Bank Transfer',
            self::Other => 'Other',
        };
    }
}
