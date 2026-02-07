<?php

declare(strict_types=1);

namespace Database\Factories\Clients;

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'company_name' => $this->faker->optional()->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->stateAbbr(),
                'postal_code' => $this->faker->postcode(),
                'country' => $this->faker->countryCode(),
            ],
            'currency_code' => 'USD',
            'language' => 'en',
            'payment_terms' => $this->faker->randomElement([0, 7, 15, 30]),
            'late_fee_type' => null,
            'late_fee_amount' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
