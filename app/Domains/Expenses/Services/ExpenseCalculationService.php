<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Models\Expense;

class ExpenseCalculationService
{
    public function calculateTaxAmount(int $amount, float $taxPercent): int
    {
        if ($taxPercent <= 0) {
            return 0;
        }

        return (int) round($amount * ($taxPercent / 100));
    }

    public function calculateMarkup(int $amount, float $markupPercent): int
    {
        if ($markupPercent <= 0) {
            return 0;
        }

        return (int) round($amount * ($markupPercent / 100));
    }

    public function calculateTotalBillable(Expense $expense): int
    {
        $total = $expense->getTotalAmount();
        $markup = $this->calculateMarkup($total, (float) ($expense->markup_percent ?? 0));

        return $total + $markup;
    }
}
