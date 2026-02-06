<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Models\Expense;
use App\Domains\Expenses\Models\RecurringExpense;

class RecurringExpenseService
{
    public function processDueProfiles(): void
    {
        RecurringExpense::query()
            ->where('is_active', true)
            ->whereDate('next_occurrence_date', '<=', now()->toDateString())
            ->get()
            ->each(function (RecurringExpense $profile): void {
                if (! $profile->shouldGenerate()) {
                    return;
                }

                $this->generateFromProfile($profile);
            });
    }

    public function generateFromProfile(RecurringExpense $profile): Expense
    {
        return $profile->generateExpense();
    }
}
