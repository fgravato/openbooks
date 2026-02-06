<?php

declare(strict_types=1);

namespace Database\Factories\Expenses;

use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseCategoryFactory extends Factory
{
    protected string $model = ExpenseCategory::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->unique()->randomElement([
                'Office Supplies',
                'Travel',
                'Meals',
                'Software',
                'Hardware',
                'Marketing',
                'Professional Services',
                'Utilities',
                'Rent',
                'Other',
            ]).'-'.$this->faker->numberBetween(1, 9999),
            'description' => $this->faker->optional()->sentence(),
            'parent_id' => null,
            'color' => $this->faker->hexColor(),
            'is_default' => false,
        ];
    }
}
