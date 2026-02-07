<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Exceptions\BankSyncException;
use App\Domains\Expenses\Models\BankConnection;
use App\Domains\Expenses\Models\BankTransaction;
use App\Domains\Expenses\Models\Expense;
use Illuminate\Support\Facades\Http;

class BankSyncService
{
    public function syncTransactions(BankConnection $connection): int
    {
        $response = Http::timeout(20)->post($this->plaidUrl('/transactions/sync'), [
            'client_id' => (string) \config('services.plaid.client_id'),
            'secret' => (string) \config('services.plaid.secret'),
            'access_token' => (string) $connection->access_token,
        ]);

        if (! $response->successful()) {
            throw new BankSyncException('Failed to sync transactions from Plaid.');
        }

        $added = (array) $response->json('added', []);
        $synced = 0;

        foreach ($added as $transaction) {
            $amount = (float) ($transaction['amount'] ?? 0);

            BankTransaction::query()->updateOrCreate(
                [
                    'organization_id' => (int) $connection->organization_id,
                    'bank_connection_id' => (int) $connection->id,
                    'transaction_id' => (string) ($transaction['transaction_id'] ?? ''),
                ],
                [
                    'amount' => (int) round($amount * 100),
                    'currency_code' => (string) ($transaction['iso_currency_code'] ?? 'USD'),
                    'date' => (string) ($transaction['date'] ?? \now()->toDateString()),
                    'name' => (string) ($transaction['name'] ?? 'Bank Transaction'),
                    'merchant_name' => isset($transaction['merchant_name']) ? (string) $transaction['merchant_name'] : null,
                    'category' => isset($transaction['category']) ? (array) $transaction['category'] : null,
                    'pending' => (bool) ($transaction['pending'] ?? false),
                ],
            );

            $synced++;
        }

        $connection->last_sync_at = \now();
        $connection->save();

        return $synced;
    }

    public function createLinkToken(): string
    {
        $response = Http::timeout(20)->post($this->plaidUrl('/link/token/create'), [
            'client_id' => (string) \config('services.plaid.client_id'),
            'secret' => (string) \config('services.plaid.secret'),
            'client_name' => 'OpenBooks',
            'language' => 'en',
            'country_codes' => ['US'],
            'user' => ['client_user_id' => (string) \auth()->id()],
            'products' => ['transactions'],
        ]);

        if (! $response->successful()) {
            throw new BankSyncException('Unable to create Plaid Link token.');
        }

        return (string) $response->json('link_token', '');
    }

    /**
     * @return array{access_token:string,item_id:string}
     */
    public function exchangePublicToken(string $publicToken): array
    {
        $response = Http::timeout(20)->post($this->plaidUrl('/item/public_token/exchange'), [
            'client_id' => (string) \config('services.plaid.client_id'),
            'secret' => (string) \config('services.plaid.secret'),
            'public_token' => $publicToken,
        ]);

        if (! $response->successful()) {
            throw new BankSyncException('Unable to exchange Plaid public token.');
        }

        return [
            'access_token' => (string) $response->json('access_token', ''),
            'item_id' => (string) $response->json('item_id', ''),
        ];
    }

    public function matchTransactionsToExpenses(BankConnection $connection): int
    {
        $matches = 0;

        $transactions = BankTransaction::query()
            ->where('organization_id', $connection->organization_id)
            ->where('bank_connection_id', $connection->id)
            ->whereNull('expense_id')
            ->get();

        foreach ($transactions as $transaction) {
            if (! $transaction->isExpense()) {
                continue;
            }

            $expense = Expense::query()
                ->where('organization_id', $connection->organization_id)
                ->where('status', ExpenseStatus::Pending->value)
                ->whereDate('date', $transaction->date)
                ->where('amount', $transaction->getAbsoluteAmount())
                ->whereNull('bank_transaction_id')
                ->first();

            if ($expense === null) {
                continue;
            }

            $transaction->expense_id = (int) $expense->id;
            $transaction->save();

            $expense->bank_transaction_id = (int) $transaction->id;
            $expense->save();
            $matches++;
        }

        return $matches;
    }

    private function plaidUrl(string $path): string
    {
        $baseUrl = rtrim((string) \config('services.plaid.base_url', 'https://sandbox.plaid.com'), '/');

        return $baseUrl.$path;
    }
}
