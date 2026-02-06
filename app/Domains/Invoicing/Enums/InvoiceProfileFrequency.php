<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Enums;

enum InvoiceProfileFrequency: string
{
    case Weekly = 'weekly';
    case BiWeekly = 'bi_weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annually = 'annually';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Weekly',
            self::BiWeekly => 'Bi-Weekly',
            self::Monthly => 'Monthly',
            self::Quarterly => 'Quarterly',
            self::Annually => 'Annually',
            self::Custom => 'Custom',
        };
    }
}
