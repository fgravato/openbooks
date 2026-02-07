<?php

declare(strict_types=1);

namespace Database\Factories\Expenses;

use App\Domains\Expenses\Models\BankConnection;
use App\Domains\Expenses\Models\BankTransaction;
use App\Domains\Expenses\Models\Expense;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankTransactionFactory extends Factory
{
    protected $model = BankTransaction::class;

    public function definition(): array
    {
        $amount = $this->faker->numberBetween(500, 200000);
        $isExpense = $this->faker->boolean(70);

        return [
            'organization_id' => Organization::factory(),
            'bank_connection_id' => BankConnection::factory(),
            'transaction_id' => 'txn_'.$this->faker->unique()->numerify('##########'),
            'amount' => $isExpense ? -$amount : $amount,
            'currency_code' => 'USD',
            'date' => $this->faker->dateTimeBetween('-90 days', 'now')->format('Y-m-d'),
            'name' => $this->faker->sentence(3),
            'merchant_name' => $this->faker->optional()->company(),
            'category' => [$this->faker->randomElement(['Travel', 'Food and Drink', 'General Merchandise'])],
            'pending' => $this->faker->boolean(15),
            'expense_id' => $this->faker->boolean(25) ? Expense::factory() : null,
        ];
    }
}
