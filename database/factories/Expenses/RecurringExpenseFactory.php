<?php

declare(strict_types=1);

namespace Database\Factories\Expenses;

use App\Domains\Expenses\Enums\RecurringExpenseFrequency;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\RecurringExpense;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringExpenseFactory extends Factory
{
    protected $model = RecurringExpense::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'vendor' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'estimated_amount' => $this->faker->numberBetween(1000, 200000),
            'frequency' => $this->faker->randomElement(RecurringExpenseFrequency::cases())->value,
            'start_date' => now()->subMonths(3)->toDateString(),
            'end_date' => $this->faker->boolean(20) ? now()->addMonths(12)->toDateString() : null,
            'next_occurrence_date' => now()->subDays($this->faker->numberBetween(0, 10))->toDateString(),
            'is_active' => true,
        ];
    }
}
