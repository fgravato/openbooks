<?php

declare(strict_types=1);

namespace Database\Factories\Expenses;

use App\Domains\Clients\Models\Client;
use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Models\Expense;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $amount = $this->faker->numberBetween(500, 150000);
        $taxPercent = $this->faker->randomElement([0, 0, 5, 7.5, 10]);
        $taxAmount = (int) round($amount * ((float) $taxPercent / 100));
        $status = $this->faker->randomElement(ExpenseStatus::cases());

        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'client_id' => $this->faker->boolean(45) ? Client::factory() : null,
            'project_id' => null,
            'category_id' => ExpenseCategory::factory(),
            'recurring_expense_id' => null,
            'vendor' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'amount' => $amount,
            'currency_code' => 'USD',
            'tax_name' => $taxPercent > 0 ? 'Sales Tax' : null,
            'tax_percent' => $taxPercent > 0 ? $taxPercent : null,
            'tax_amount' => $taxAmount,
            'date' => $this->faker->dateTimeBetween('-120 days', 'now')->format('Y-m-d'),
            'receipt_path' => null,
            'status' => $status->value,
            'is_billable' => $this->faker->boolean(40),
            'is_reimbursable' => $this->faker->boolean(35),
            'markup_percent' => $this->faker->boolean(30) ? $this->faker->randomFloat(2, 0, 25) : null,
            'invoice_id' => $status === ExpenseStatus::Billed ? Invoice::factory() : null,
            'bank_transaction_id' => null,
            'notes' => $this->faker->optional()->sentence(),
            'approved_by_user_id' => in_array($status, [ExpenseStatus::Approved, ExpenseStatus::Reimbursed, ExpenseStatus::Billed], true)
                ? User::factory()
                : null,
            'approved_at' => in_array($status, [ExpenseStatus::Approved, ExpenseStatus::Reimbursed, ExpenseStatus::Billed], true)
                ? $this->faker->dateTimeBetween('-60 days', 'now')
                : null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Expense $expense): void {
            $organizationId = (int) $expense->organization_id;

            if ($expense->user !== null && (int) $expense->user->organization_id !== $organizationId) {
                $expense->user->organization_id = $organizationId;
                $expense->user->save();
            }

            if ($expense->client !== null && (int) $expense->client->organization_id !== $organizationId) {
                $expense->client->organization_id = $organizationId;
                $expense->client->save();
            }

            if ((int) $expense->category->organization_id !== $organizationId) {
                $expense->category->organization_id = $organizationId;
                $expense->category->save();
            }
        });
    }
}
