<?php

declare(strict_types=1);

namespace Database\Factories\Payments;

use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentRefund;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentRefundFactory extends Factory
{
    protected $model = PaymentRefund::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'organization_id' => Organization::factory(),
            'amount' => $this->faker->numberBetween(500, 50000),
            'reason' => $this->faker->randomElement([
                'Customer requested refund',
                'Duplicate charge',
                'Service issue',
            ]),
            'gateway_refund_id' => 're_'.$this->faker->unique()->bothify('##########'),
            'refunded_by_user_id' => User::factory(),
            'refunded_at' => $this->faker->dateTimeBetween('-20 days', 'now'),
        ];
    }
}
