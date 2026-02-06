<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Expenses\Services\RecurringExpenseService;
use Illuminate\Console\Command;

class ProcessRecurringExpenses extends Command
{
    protected string $signature = 'expenses:process-recurring';

    protected string $description = 'Generate expenses from due recurring profiles';

    public function handle(RecurringExpenseService $recurringExpenseService): int
    {
        $recurringExpenseService->processDueProfiles();

        $this->info('Recurring expenses processed successfully.');

        return self::SUCCESS;
    }
}
