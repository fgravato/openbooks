<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Services;

use App\Domains\Identity\Models\Organization;
use App\Domains\Invoicing\Exceptions\DuplicateInvoiceNumberException;
use App\Domains\Invoicing\Models\Invoice;

class InvoiceNumberService
{
    public function generateNextNumber(Organization $organization): string
    {
        $year = \now()->format('Y');
        $prefix = "INV-{$year}-";

        $latestNumber = Invoice::query()
            ->withoutGlobalScopes()
            ->where('organization_id', $organization->id)
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $startingNumber = max(1, (int) ($organization->settings['invoicing']['starting_number'] ?? 1));
        $next = $startingNumber;

        if (is_string($latestNumber)) {
            $parts = explode('-', $latestNumber);
            $sequence = (int) ($parts[2] ?? 0);
            $next = max(1, $sequence + 1);
        }

        return sprintf('%s%05d', $prefix, $next);
    }

    public function validateUnique(string $number, Organization $organization): bool
    {
        return ! Invoice::query()
            ->withoutGlobalScopes()
            ->where('organization_id', $organization->id)
            ->where('invoice_number', $number)
            ->exists();
    }

    public function setStartingNumber(Organization $organization, int $start): void
    {
        $start = max(1, $start);
        $existingSettings = is_array($organization->settings) ? $organization->settings : [];
        $invoicingSettings = is_array($existingSettings['invoicing'] ?? null)
            ? $existingSettings['invoicing']
            : [];

        $invoicingSettings['starting_number'] = $start;
        $existingSettings['invoicing'] = $invoicingSettings;

        $organization->settings = $existingSettings;
        $organization->save();

        $candidate = sprintf('INV-%s-%05d', \now()->format('Y'), $start);

        if (! $this->validateUnique($candidate, $organization)) {
            throw new DuplicateInvoiceNumberException($candidate, $organization->id);
        }
    }
}
