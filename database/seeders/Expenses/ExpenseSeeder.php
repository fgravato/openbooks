<?php

declare(strict_types=1);

namespace Database\Seeders\Expenses;

use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Models\Expense;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        Organization::query()->each(function (Organization $organization): void {
            $users = User::query()->where('organization_id', $organization->id)->get();
            $categories = ExpenseCategory::query()->where('organization_id', $organization->id)->get();

            if ($users->isEmpty() || $categories->isEmpty()) {
                return;
            }

            for ($i = 0; $i < 30; $i++) {
                $status = fake()->randomElement([
                    ExpenseStatus::Pending,
                    ExpenseStatus::Pending,
                    ExpenseStatus::Approved,
                    ExpenseStatus::Rejected,
                    ExpenseStatus::Reimbursed,
                    ExpenseStatus::Billed,
                ]);

                $user = $users->random();
                $approver = $users->count() > 1 ? $users->where('id', '!=', $user->id)->random() : $user;
                $amount = fake()->numberBetween(1000, 100000);
                $taxPercent = fake()->randomElement([0, 0, 5, 10]);
                $taxAmount = (int) round($amount * (((float) $taxPercent) / 100));

                Expense::query()->create([
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                    'category_id' => $categories->random()->id,
                    'vendor' => fake()->company(),
                    'description' => fake()->sentence(),
                    'amount' => $amount,
                    'currency_code' => $organization->currency_code,
                    'tax_name' => $taxPercent > 0 ? 'Sales Tax' : null,
                    'tax_percent' => $taxPercent > 0 ? $taxPercent : null,
                    'tax_amount' => $taxAmount,
                    'date' => fake()->dateTimeBetween('-120 days', 'now')->format('Y-m-d'),
                    'status' => $status,
                    'is_billable' => fake()->boolean(40),
                    'is_reimbursable' => fake()->boolean(30),
                    'markup_percent' => fake()->boolean(25) ? fake()->randomFloat(2, 0, 20) : null,
                    'notes' => fake()->optional()->sentence(),
                    'approved_by_user_id' => in_array($status, [ExpenseStatus::Approved, ExpenseStatus::Reimbursed, ExpenseStatus::Billed], true)
                        ? $approver->id
                        : null,
                    'approved_at' => in_array($status, [ExpenseStatus::Approved, ExpenseStatus::Reimbursed, ExpenseStatus::Billed], true)
                        ? now()->subDays(random_int(1, 45))
                        : null,
                ]);
            }
        });
    }
}
