<?php

declare(strict_types=1);

namespace Database\Seeders\Identity;

use App\Domains\Identity\Enums\SubscriptionTier;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        Organization::query()->firstOrCreate(
            ['slug' => 'acme'],
            [
                'name' => 'Acme Corporation',
                'owner_id' => null,
                'currency_code' => 'USD',
                'timezone' => 'UTC',
                'logo_path' => null,
                'settings' => [
                    'features' => [],
                ],
                'subscription_tier' => SubscriptionTier::Plus->value,
            ],
        );
    }
}
