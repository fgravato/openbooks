<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Models\Expense;
use App\Domains\Identity\Models\User;
use App\Scopes\OrganizationScope;
use DateTime;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ExpenseImportService
{
    /**
     * @param  array<string, string>  $mappings
     * @return array{imported:int, errors:array<int, string>}
     */
    public function importFromCsv(string $filePath, int $categoryId, array $mappings): array
    {
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to open CSV file for import.');
        }

        $organizationId = OrganizationScope::resolveCurrentOrganizationId();
        $user = Auth::user();

        if (! $user instanceof User || $organizationId === null) {
            fclose($handle);
            throw new RuntimeException('Expense import requires an authenticated organization user.');
        }

        $header = fgetcsv($handle);
        $imported = 0;
        $errors = [];
        $lineNumber = 1;

        while (($raw = fgetcsv($handle)) !== false) {
            $lineNumber++;
            $row = [];

            foreach ((array) $header as $index => $column) {
                $row[(string) $column] = $raw[$index] ?? null;
            }

            $normalized = [
                'vendor' => $row[$mappings['vendor'] ?? 'vendor'] ?? null,
                'amount' => $row[$mappings['amount'] ?? 'amount'] ?? null,
                'date' => $row[$mappings['date'] ?? 'date'] ?? null,
            ];

            if (! $this->validateRow($normalized)) {
                $errors[] = "Line {$lineNumber}: invalid row data.";

                continue;
            }

            $amountSource = (string) ($row[$mappings['amount'] ?? 'amount'] ?? '0');
            $amount = (int) round(((float) $amountSource) * 100);

            Expense::query()->create([
                'organization_id' => $organizationId,
                'user_id' => (int) $user->id,
                'category_id' => $categoryId,
                'vendor' => (string) ($row[$mappings['vendor'] ?? 'vendor'] ?? 'Unknown Vendor'),
                'description' => (string) ($row[$mappings['description'] ?? 'description'] ?? ''),
                'amount' => $amount,
                'currency_code' => (string) ($row[$mappings['currency'] ?? 'currency_code'] ?? 'USD'),
                'tax_name' => null,
                'tax_percent' => 0,
                'tax_amount' => 0,
                'date' => new DateTime((string) ($row[$mappings['date'] ?? 'date'] ?? \now()->toDateString())),
                'status' => ExpenseStatus::Pending,
                'is_billable' => false,
                'is_reimbursable' => false,
                'markup_percent' => null,
            ]);

            $imported++;
        }

        fclose($handle);

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * @return array{imported:int, errors:array<int, string>}
     */
    public function importFromOfx(string $filePath): array
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new RuntimeException('Unable to read OFX file.');
        }

        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $count = 0;

        foreach ($lines as $line) {
            if (str_contains($line, '<STMTTRN>')) {
                $count++;
            }
        }

        return ['imported' => $count, 'errors' => []];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function validateRow(array $row): bool
    {
        $vendor = isset($row['vendor']) ? trim((string) $row['vendor']) : '';
        $amount = isset($row['amount']) ? (float) $row['amount'] : null;
        $date = isset($row['date']) ? (string) $row['date'] : '';

        if ($vendor === '' || $amount === null) {
            return false;
        }

        return strtotime($date) !== false;
    }
}
