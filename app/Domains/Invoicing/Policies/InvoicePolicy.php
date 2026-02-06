<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Policies;

use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Domains\Invoicing\Models\Invoice;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->organization_id === $invoice->organization_id
            && ($user->hasPermission('invoices.view') || $user->hasPermission('invoices.manage'));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('invoices.manage');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->organization_id === $invoice->organization_id
            && $invoice->canBeEdited()
            && $user->hasPermission('invoices.manage');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->organization_id === $invoice->organization_id
            && $invoice->status === InvoiceStatus::Draft
            && $user->hasPermission('invoices.manage');
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $user->organization_id === $invoice->organization_id
            && $invoice->status === InvoiceStatus::Draft
            && $user->hasPermission('invoices.manage');
    }

    public function duplicate(User $user, Invoice $invoice): bool
    {
        return $user->organization_id === $invoice->organization_id
            && ($user->hasPermission('invoices.view') || $user->hasPermission('invoices.manage'));
    }
}
