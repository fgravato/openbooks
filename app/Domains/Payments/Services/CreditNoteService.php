<?php

declare(strict_types=1);

namespace App\Domains\Payments\Services;

use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Payments\DTOs\CreditNoteData;
use App\Domains\Payments\Exceptions\PaymentProcessingException;
use App\Domains\Payments\Models\CreditNote;
use Illuminate\Support\Facades\DB;

class CreditNoteService
{
    public function createCreditNote(CreditNoteData $data): CreditNote
    {
        $user = \auth()->user();

        if ($user === null) {
            throw new PaymentProcessingException('Authenticated user is required to create a credit note.');
        }

        return CreditNote::query()->create([
            'organization_id' => $user->organization_id,
            'client_id' => $data->clientId,
            'credit_note_number' => $this->generateNumber((int) $user->organization_id),
            'amount' => $data->amount,
            'remaining_amount' => $data->amount,
            'reason' => $data->reason,
            'invoice_id' => $data->invoiceId,
            'created_by_user_id' => $user->id,
        ]);
    }

    public function applyCreditNote(CreditNote $creditNote, Invoice $invoice, ?int $amount = null): void
    {
        if ((int) $creditNote->remaining_amount <= 0) {
            throw new PaymentProcessingException('Credit note has no remaining balance.');
        }

        $applicationAmount = $amount ?? min((int) $creditNote->remaining_amount, (int) $invoice->amount_outstanding);

        if ($applicationAmount <= 0) {
            throw new PaymentProcessingException('Credit note application amount must be greater than zero.');
        }

        DB::transaction(function () use ($creditNote, $invoice, $applicationAmount): void {
            $creditNote->applyToInvoice($invoice, $applicationAmount);

            $invoice->amount_paid = max(0, (int) $invoice->amount_paid + $applicationAmount);
            $invoice->amount_outstanding = max(0, (int) $invoice->total - (int) $invoice->amount_paid);

            if ((int) $invoice->amount_outstanding === 0) {
                $invoice->status = InvoiceStatus::Paid;
                $invoice->paid_at = \now();
            } elseif ((int) $invoice->amount_paid > 0 && $invoice->status !== InvoiceStatus::Cancelled) {
                $invoice->status = InvoiceStatus::Partial;
            }

            $invoice->save();
        });
    }

    public function voidCreditNote(CreditNote $creditNote): void
    {
        if ($creditNote->appliedTo()->exists()) {
            throw new PaymentProcessingException('Applied credit notes cannot be voided.');
        }

        $creditNote->remaining_amount = 0;
        $creditNote->save();
    }

    private function generateNumber(int $organizationId): string
    {
        $year = (string) \now()->format('Y');
        $latest = CreditNote::query()
            ->withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('credit_note_number', 'like', "CN-{$year}-%")
            ->orderByDesc('id')
            ->value('credit_note_number');

        $sequence = 1;

        if (is_string($latest)) {
            $parts = explode('-', $latest);
            $sequence = ((int) ($parts[2] ?? 0)) + 1;
        }

        return sprintf('CN-%s-%05d', $year, $sequence);
    }
}
