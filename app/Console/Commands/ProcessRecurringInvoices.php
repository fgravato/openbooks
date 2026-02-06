<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Invoicing\Services\RecurringInvoiceService;
use Illuminate\Console\Command;

class ProcessRecurringInvoices extends Command
{
    protected $signature = 'invoices:process-recurring';

    protected $description = 'Generate invoices from due recurring profiles';

    public function handle(RecurringInvoiceService $recurringInvoiceService): int
    {
        $recurringInvoiceService->processDueProfiles();

        $this->info('Recurring invoices processed successfully.');

        return self::SUCCESS;
    }
}
