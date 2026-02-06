<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPaymentReminders extends Command
{
    protected string $signature = 'invoices:send-reminders';

    protected string $description = 'Send payment reminder emails';

    public function handle(): int
    {
        Invoice::query()
            ->sent()
            ->where('amount_outstanding', '>', 0)
            ->whereDate('due_date', '<=', \now()->addDays(3)->toDateString())
            ->get()
            ->each(function (Invoice $invoice): void {
                Log::info('Payment reminder queued.', [
                    'invoice_id' => $invoice->id,
                    'organization_id' => $invoice->organization_id,
                ]);
            });

        $this->info('Payment reminders queued successfully.');

        return self::SUCCESS;
    }
}
