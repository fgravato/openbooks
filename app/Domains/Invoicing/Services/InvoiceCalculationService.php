<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Enums\DiscountType;
use App\Domains\Invoicing\Models\Invoice;

class InvoiceCalculationService
{
    public function calculateSubtotal(Invoice $invoice): int
    {
        return (int) $invoice->lines()->get()->sum(static fn ($line): int => $line->calculateAmount());
    }

    public function calculateTaxAmount(Invoice $invoice): int
    {
        return (int) $invoice->lines()->get()->sum(static fn ($line): int => $line->getTaxAmount());
    }

    public function calculateDiscount(Invoice $invoice, int $subtotal): int
    {
        if (! $invoice->discount_type instanceof DiscountType || (int) $invoice->discount_value <= 0) {
            return 0;
        }

        $discount = $invoice->discount_type->calculateDiscount((float) $subtotal, (float) $invoice->discount_value);

        return max(0, min($subtotal, (int) round($discount)));
    }

    public function calculateTotal(Invoice $invoice): int
    {
        $subtotal = $this->calculateSubtotal($invoice);
        $taxAmount = $this->calculateTaxAmount($invoice);
        $discount = $this->calculateDiscount($invoice, $subtotal);

        return max(0, $subtotal + $taxAmount - $discount);
    }

    public function recalculate(Invoice $invoice): void
    {
        $subtotal = $this->calculateSubtotal($invoice);
        $taxAmount = $this->calculateTaxAmount($invoice);
        $discount = $this->calculateDiscount($invoice, $subtotal);
        $total = max(0, $subtotal + $taxAmount - $discount);

        $invoice->subtotal = $subtotal;
        $invoice->tax_amount = $taxAmount;
        $invoice->total = $total;
        $invoice->amount_outstanding = max(0, $total - (int) $invoice->amount_paid);
        $invoice->save();
    }
}
