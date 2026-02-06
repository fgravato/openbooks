<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    protected string $signature = 'invoices:check-overdue';

    protected string $description = 'Update status to Overdue for past-due invoices';

    public function handle(): int
    {
        $updated = Invoice::query()
            ->whereDate('due_date', '<', \now()->toDateString())
            ->whereNotIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::Cancelled->value])
            ->update(['status' => InvoiceStatus::Overdue->value]);

        $this->info("Updated {$updated} invoice(s) to overdue.");

        return self::SUCCESS;
    }
}
