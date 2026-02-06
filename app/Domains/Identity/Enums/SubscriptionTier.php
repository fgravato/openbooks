<?php

declare(strict_types=1);

namespace App\Domains\Identity\Enums;

enum SubscriptionTier: string
{
    case Lite = 'lite';
    case Plus = 'plus';
    case Premium = 'premium';
    case Select = 'select';

    public function label(): string
    {
        return match ($this) {
            self::Lite => 'Lite',
            self::Plus => 'Plus',
            self::Premium => 'Premium',
            self::Select => 'Select',
        };
    }

    public function features(): array
    {
        return match ($this) {
            self::Lite => [
                'invoices',
                'clients',
                'expenses',
                'basic_reports',
            ],
            self::Plus => [
                'invoices',
                'clients',
                'expenses',
                'projects',
                'time_tracking',
                'automation',
                'advanced_reports',
            ],
            self::Premium => [
                'invoices',
                'clients',
                'expenses',
                'projects',
                'time_tracking',
                'automation',
                'advanced_reports',
                'multi_currency',
                'approvals',
                'api_access',
            ],
            self::Select => [
                'invoices',
                'clients',
                'expenses',
                'projects',
                'time_tracking',
                'automation',
                'advanced_reports',
                'multi_currency',
                'approvals',
                'api_access',
                'sso',
                'audit_logs',
                'custom_roles',
                'priority_support',
            ],
        };
    }
}
