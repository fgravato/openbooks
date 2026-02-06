<?php

declare(strict_types=1);

namespace Database\Factories\Invoicing;

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Invoicing\Enums\InvoiceProfileFrequency;
use App\Domains\Invoicing\Models\InvoiceProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceProfileFactory extends Factory
{
    protected string $model = InvoiceProfile::class;

    public function definition(): array
    {
        $frequency = $this->faker->randomElement(InvoiceProfileFrequency::cases());

        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'name' => $this->faker->randomElement([
                'Monthly Retainer',
                'Maintenance Subscription',
                'Managed Services Plan',
                'Support Agreement',
                'Recurring Product License',
            ]),
            'frequency' => $frequency->value,
            'custom_days' => $frequency === InvoiceProfileFrequency::Custom ? $this->faker->numberBetween(10, 45) : null,
            'next_issue_date' => \now()->addDays($this->faker->numberBetween(-10, 20))->toDateString(),
            'end_date' => $this->faker->boolean(20)
                ? \now()->addMonths($this->faker->numberBetween(2, 18))->toDateString()
                : null,
            'occurrences_remaining' => $this->faker->boolean(35) ? $this->faker->numberBetween(1, 18) : null,
            'auto_send' => $this->faker->boolean(60),
            'template_data' => [
                'payment_terms_days' => $this->faker->randomElement([7, 15, 30]),
                'currency_code' => 'USD',
                'template' => $this->faker->randomElement(['default', 'modern', 'classic']),
                'lines' => [
                    [
                        'type' => 'item',
                        'description' => 'Recurring service package',
                        'quantity' => 1,
                        'unit_price' => $this->faker->numberBetween(35000, 150000),
                        'tax_name' => 'Sales Tax',
                        'tax_percent' => $this->faker->randomElement([0, 5, 10]),
                    ],
                ],
            ],
            'is_active' => true,
            'last_generated_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (InvoiceProfile $profile): void {
            if ($profile->client->organization_id !== $profile->organization_id) {
                $profile->client->organization_id = $profile->organization_id;
                $profile->client->currency_code = $profile->organization->currency_code;
                $profile->client->save();
            }
        });
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
