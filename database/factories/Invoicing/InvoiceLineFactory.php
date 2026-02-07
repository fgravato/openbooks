<?php

declare(strict_types=1);

namespace Database\Factories\Invoicing;

use App\Domains\Invoicing\Enums\InvoiceLineType;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceLineFactory extends Factory
{
    protected $model = InvoiceLine::class;

    public function definition(): array
    {
        $quantity = (float) $this->faker->randomElement([1, 1, 1, 2, 3, 4, 8]);
        $unitPrice = $this->faker->numberBetween(1500, 25000);
        $taxPercent = $this->faker->randomElement([0, 5, 8.25, 10, 20]);

        return [
            'invoice_id' => Invoice::factory(),
            'type' => InvoiceLineType::Item->value,
            'description' => $this->faker->randomElement([
                'Product design retainer',
                'Discovery workshop',
                'Backend API development',
                'Consulting hours',
                'UI implementation sprint',
            ]),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_name' => $taxPercent > 0 ? 'Sales Tax' : null,
            'tax_percent' => $taxPercent > 0 ? $taxPercent : null,
            'amount' => (int) round($quantity * $unitPrice),
            'expense_id' => null,
            'time_entry_id' => null,
            'sort_order' => $this->faker->numberBetween(1, 15),
        ];
    }

    public function item(): self
    {
        return $this->state(fn (): array => [
            'type' => InvoiceLineType::Item->value,
            'description' => $this->faker->randomElement([
                'Premium support package',
                'System setup',
                'Implementation milestone',
            ]),
            'expense_id' => null,
            'time_entry_id' => null,
        ]);
    }

    public function time(): self
    {
        $hours = (float) $this->faker->randomElement([0.5, 1, 2, 4, 6, 8]);
        $rate = $this->faker->numberBetween(7500, 22000);

        return $this->state(fn (): array => [
            'type' => InvoiceLineType::Time->value,
            'description' => $this->faker->randomElement([
                'Engineering time block',
                'Technical architecture session',
                'Quality assurance pass',
            ]),
            'quantity' => $hours,
            'unit_price' => $rate,
            'amount' => (int) round($hours * $rate),
            'time_entry_id' => null,
            'expense_id' => null,
        ]);
    }

    public function expense(): self
    {
        return $this->state(fn (): array => [
            'type' => InvoiceLineType::Expense->value,
            'description' => $this->faker->randomElement([
                'Travel reimbursement',
                'Third-party software license',
                'Cloud hosting allocation',
            ]),
            'quantity' => 1,
            'unit_price' => $this->faker->numberBetween(1200, 50000),
            'amount' => fn (array $attributes): int => (int) $attributes['unit_price'],
            'expense_id' => null,
            'time_entry_id' => null,
        ]);
    }
}
