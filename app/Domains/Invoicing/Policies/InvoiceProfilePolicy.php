<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Policies;

use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Models\InvoiceProfile;

class InvoiceProfilePolicy
{
    public function view(User $user, InvoiceProfile $profile): bool
    {
        return $user->organization_id === $profile->organization_id
            && ($user->hasPermission('invoices.view') || $user->hasPermission('invoices.manage'));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('invoices.manage');
    }

    public function update(User $user, InvoiceProfile $profile): bool
    {
        return $user->organization_id === $profile->organization_id
            && $user->hasPermission('invoices.manage');
    }

    public function delete(User $user, InvoiceProfile $profile): bool
    {
        return $user->organization_id === $profile->organization_id
            && $user->hasPermission('invoices.manage');
    }

    public function duplicate(User $user, InvoiceProfile $profile): bool
    {
        return $user->organization_id === $profile->organization_id
            && $user->hasPermission('invoices.manage');
    }
}
