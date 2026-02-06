<?php

declare(strict_types=1);

namespace Database\Factories\Payments;

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Payments\Models\CreditNote;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditNoteFactory extends Factory
{
    protected string $model = CreditNote::class;

    public function definition(): array
    {
        $amount = $this->faker->numberBetween(1000, 75000);

        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'credit_note_number' => 'CN-'.(string) \now()->format('Y').'-'.$this->faker->unique()->numerify('#####'),
            'amount' => $amount,
            'remaining_amount' => $amount,
            'reason' => $this->faker->sentence(),
            'invoice_id' => $this->faker->boolean(35) ? Invoice::factory() : null,
            'created_by_user_id' => User::factory(),
        ];
    }
}
