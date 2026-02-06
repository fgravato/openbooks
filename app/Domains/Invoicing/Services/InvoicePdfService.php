<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceTemplate;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    public function generate(Invoice $invoice, ?InvoiceTemplate $template = null): string
    {
        $selectedTemplate = $template
            ?? InvoiceTemplate::query()
                ->where('organization_id', $invoice->organization_id)
                ->where('is_default', true)
                ->first();

        $html = $selectedTemplate?->render($invoice) ?? "<h1>Invoice {$invoice->invoice_number}</h1>";
        $path = sprintf('invoices/%d/%s.html', $invoice->organization_id, $invoice->invoice_number);

        Storage::disk('local')->put($path, $html);

        return Storage::disk('local')->path($path);
    }

    public function getPdfContent(Invoice $invoice): string
    {
        $path = sprintf('invoices/%d/%s.html', $invoice->organization_id, $invoice->invoice_number);

        if (! Storage::disk('local')->exists($path)) {
            $this->generate($invoice);
        }

        return (string) Storage::disk('local')->get($path);
    }

    public function deleteOldPdf(Invoice $invoice): void
    {
        $path = sprintf('invoices/%d/%s.html', $invoice->organization_id, $invoice->invoice_number);

        Storage::disk('local')->delete($path);
    }
}
