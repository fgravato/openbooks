<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Domains\Invoicing\Exceptions\InvalidStatusTransitionException;
use App\Domains\Invoicing\Models\Invoice;

class InvoiceStatusService
{
    public function transition(Invoice $invoice, InvoiceStatus $newStatus): bool
    {
        if (! $this->canTransition($invoice, $newStatus)) {
            throw new InvalidStatusTransitionException($invoice->status, $newStatus);
        }

        $invoice->status = $newStatus;

        if ($newStatus === InvoiceStatus::Sent) {
            $invoice->sent_at = \now();
        }

        if ($newStatus === InvoiceStatus::Viewed) {
            $invoice->viewed_at = \now();
        }

        if ($newStatus === InvoiceStatus::Paid) {
            $invoice->paid_at = \now();
        }

        return $invoice->save();
    }

    public function canTransition(Invoice $invoice, InvoiceStatus $newStatus): bool
    {
        return $invoice->status->canTransitionTo($newStatus);
    }

    public function autoUpdateStatus(Invoice $invoice): void
    {
        if ($invoice->isOverdue() && $invoice->status->canTransitionTo(InvoiceStatus::Overdue)) {
            $invoice->status = InvoiceStatus::Overdue;
            $invoice->save();
        }
    }
}
