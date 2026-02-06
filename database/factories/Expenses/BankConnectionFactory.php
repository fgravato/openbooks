<?php

declare(strict_types=1);

namespace Database\Factories\Expenses;

use App\Domains\Expenses\Enums\BankAccountType;
use App\Domains\Expenses\Models\BankConnection;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankConnectionFactory extends Factory
{
    protected string $model = BankConnection::class;

    public function definition(): array
    {
        $accountType = $this->faker->randomElement(BankAccountType::cases());

        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->randomElement(['Business Checking', 'Business Savings', 'Business Credit']),
            'institution_name' => $this->faker->company().' Bank',
            'institution_id' => 'ins_'.$this->faker->unique()->numerify('######'),
            'access_token' => 'access-sandbox-'.$this->faker->sha1(),
            'item_id' => 'item_'.$this->faker->unique()->numerify('######'),
            'account_mask' => (string) $this->faker->numberBetween(1000, 9999),
            'account_type' => $accountType->value,
            'balance_current' => $this->faker->numberBetween(0, 5_000_000),
            'balance_available' => $this->faker->numberBetween(0, 4_500_000),
            'currency_code' => 'USD',
            'last_sync_at' => $this->faker->optional()->dateTimeBetween('-2 days', 'now'),
            'is_active' => true,
        ];
    }
}
