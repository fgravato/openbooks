<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Expenses\Models\BankConnection;
use App\Domains\Expenses\Services\BankSyncService;
use Illuminate\Console\Command;

class SyncBankTransactions extends Command
{
    protected string $signature = 'bank:sync';

    protected string $description = 'Sync transactions from connected bank accounts';

    public function handle(BankSyncService $bankSyncService): int
    {
        $connections = BankConnection::query()->where('is_active', true)->get();
        $syncedTotal = 0;
        $matchedTotal = 0;

        foreach ($connections as $connection) {
            if (! $connection->needsSync()) {
                continue;
            }

            $syncedTotal += $bankSyncService->syncTransactions($connection);
            $matchedTotal += $bankSyncService->matchTransactionsToExpenses($connection);
        }

        $this->info("Bank sync complete. Synced {$syncedTotal} transaction(s), matched {$matchedTotal} expense(s).");

        return self::SUCCESS;
    }
}
