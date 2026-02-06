<?php

declare(strict_types=1);

namespace Database\Seeders\Expenses;

use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
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
        ];

        Organization::query()->each(function (Organization $organization) use ($defaults): void {
            foreach ($defaults as $name) {
                ExpenseCategory::query()->firstOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'name' => $name,
                    ],
                    [
                        'description' => null,
                        'parent_id' => null,
                        'color' => '#64748b',
                        'is_default' => true,
                    ],
                );
            }
        });
    }
}
