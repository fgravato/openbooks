<?php

declare(strict_types=1);

namespace App\Domains\Payments\Policies;

use App\Domains\Identity\Models\User;
use App\Domains\Payments\Models\Payment;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $user->organization_id === $payment->organization_id
            && ($user->hasPermission('payments.manage') || $user->hasPermission('invoices.view'));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('payments.manage');
    }

    public function refund(User $user, Payment $payment): bool
    {
        return $user->organization_id === $payment->organization_id
            && $user->hasPermission('payments.manage')
            && $payment->isRefundable();
    }

    public function viewReceipt(User $user, Payment $payment): bool
    {
        return $user->organization_id === $payment->organization_id
            && ($user->hasPermission('payments.manage') || $user->hasPermission('invoices.view'));
    }
}
