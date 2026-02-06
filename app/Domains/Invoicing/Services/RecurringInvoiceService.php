<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceProfile;
use Illuminate\Support\Facades\Log;

class RecurringInvoiceService
{
    public function processDueProfiles(): void
    {
        InvoiceProfile::query()
            ->where('is_active', true)
            ->whereDate('next_issue_date', '<=', \now()->toDateString())
            ->get()
            ->each(function (InvoiceProfile $profile): void {
                if (! $profile->shouldGenerate()) {
                    return;
                }

                $invoice = $this->generateFromProfile($profile);

                if ($profile->auto_send) {
                    $this->sendGeneratedInvoice($invoice);
                }
            });
    }

    public function generateFromProfile(InvoiceProfile $profile): Invoice
    {
        return $profile->generateInvoice();
    }

    public function sendGeneratedInvoice(Invoice $invoice): void
    {
        $invoice->markAsSent();

        Log::info('Recurring invoice generated and sent.', [
            'invoice_id' => $invoice->id,
            'organization_id' => $invoice->organization_id,
        ]);
    }
}
