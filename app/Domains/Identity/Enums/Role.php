<?php

declare(strict_types=1);

namespace App\Domains\Identity\Enums;

enum Role: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Manager = 'manager';
    case Employee = 'employee';
    case Contractor = 'contractor';
    case Accountant = 'accountant';
    case Client = 'client';

    public function permissions(): array
    {
        return match ($this) {
            self::Owner => ['*'],
            self::Admin => [
                'users.manage',
                'clients.manage',
                'projects.manage',
                'invoices.manage',
                'expenses.manage',
                'reports.view',
                'settings.manage',
            ],
            self::Manager => [
                'clients.manage',
                'projects.manage',
                'invoices.manage',
                'expenses.view',
                'time.manage',
                'reports.view',
            ],
            self::Employee => [
                'time.create',
                'time.view',
                'expenses.create',
                'expenses.view_own',
                'projects.view',
            ],
            self::Contractor => [
                'time.create',
                'time.view_own',
                'projects.view_assigned',
            ],
            self::Accountant => [
                'invoices.view',
                'payments.manage',
                'expenses.manage',
                'reports.view',
                'taxes.manage',
            ],
            self::Client => [
                'portal.view',
                'invoices.view_own',
                'estimates.view_own',
            ],
        };
    }
}
