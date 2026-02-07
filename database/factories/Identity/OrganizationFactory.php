<?php

declare(strict_types=1);

namespace Database\Factories\Identity;

use App\Domains\Identity\Enums\SubscriptionTier;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $companyName = $this->faker->unique()->company();

        return [
            'name' => $companyName,
            'slug' => Str::slug($companyName).'-'.$this->faker->unique()->numberBetween(100, 999),
            'owner_id' => null,
            'currency_code' => 'USD',
            'timezone' => 'UTC',
            'logo_path' => null,
            'settings' => [
                'features' => [],
            ],
            'subscription_tier' => $this->faker->randomElement(SubscriptionTier::cases())->value,
        ];
    }
}
