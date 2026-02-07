<?php

declare(strict_types=1);

namespace Database\Seeders\Invoicing;

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Enums\InvoiceStatus;
use Database\Factories\Invoicing\InvoiceFactory;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        Organization::query()->each(function (Organization $organization): void {
            $clients = Client::query()->where('organization_id', $organization->id)->get();

            if ($clients->isEmpty()) {
                $clients = Client::factory()->count(12)->create([
                    'organization_id' => $organization->id,
                    'currency_code' => $organization->currency_code,
                ]);
            }

            $users = User::query()->where('organization_id', $organization->id)->get();

            if ($users->isEmpty()) {
                $users = User::factory()->count(3)->create([
                    'organization_id' => $organization->id,
                ]);
            }

            for ($index = 0; $index < 50; $index++) {
                $invoice = InvoiceFactory::new()->create([
                    'organization_id' => $organization->id,
                    'client_id' => $clients->random()->id,
                    'created_by_user_id' => $users->random()->id,
                    'currency_code' => $organization->currency_code,
                ]);

                if ($invoice->status === InvoiceStatus::Overdue) {
                    $invoice->due_date = \now()->subDays(random_int(5, 40))->toDateString();
                    $invoice->save();
                }

                if ($invoice->status === InvoiceStatus::Partial) {
                    $invoice->amount_paid = (int) round(((int) $invoice->total) * 0.35);
                    $invoice->amount_outstanding = max(0, (int) $invoice->total - (int) $invoice->amount_paid);
                    $invoice->save();
                }

                if ($invoice->status === InvoiceStatus::Paid) {
                    $invoice->amount_paid = (int) $invoice->total;
                    $invoice->amount_outstanding = 0;
                    $invoice->paid_at = $invoice->paid_at ?? \now();
                    $invoice->save();
                }
            }
        });
    }
}
