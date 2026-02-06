<?php

declare(strict_types=1);

namespace Database\Seeders\Invoicing;

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use Database\Factories\Invoicing\InvoiceProfileFactory;
use Illuminate\Database\Seeder;

class InvoiceProfileSeeder extends Seeder
{
    public function run(): void
    {
        Organization::query()->each(function (Organization $organization): void {
            $clients = Client::query()->where('organization_id', $organization->id)->get();

            if ($clients->isEmpty()) {
                $clients = Client::factory()->count(5)->create([
                    'organization_id' => $organization->id,
                    'currency_code' => $organization->currency_code,
                ]);
            }

            for ($index = 0; $index < 5; $index++) {
                InvoiceProfileFactory::new()->create([
                    'organization_id' => $organization->id,
                    'client_id' => $clients->random()->id,
                ]);
            }
        });
    }
}
