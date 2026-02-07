<?php

declare(strict_types=1);

namespace Database\Factories\Payments;

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Payments\Enums\PaymentGateway;
use App\Domains\Payments\Enums\PaymentMethod;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $amount = $this->faker->numberBetween(1000, 100000);
        $fee = $this->faker->numberBetween(0, (int) round($amount * 0.05));
        $methods = PaymentMethod::cases();
        $method = $this->faker->randomElement($methods);
        $status = $this->faker->randomElement(PaymentStatus::cases());

        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'invoice_id' => Invoice::factory(),
            'amount' => $amount,
            'currency_code' => 'USD',
            'method' => $method->value,
            'status' => $status->value,
            'gateway' => $method->isOnline() ? PaymentGateway::Stripe->value : PaymentGateway::Manual->value,
            'gateway_transaction_id' => $method->isOnline() ? 'pi_'.$this->faker->unique()->bothify('##########') : null,
            'gateway_fee_amount' => $fee,
            'net_amount' => max(0, $amount - $fee),
            'paid_at' => in_array($status, [PaymentStatus::Completed, PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded], true)
                ? $this->faker->dateTimeBetween('-60 days', 'now')
                : null,
            'refunded_at' => in_array($status, [PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded], true)
                ? $this->faker->dateTimeBetween('-30 days', 'now')
                : null,
            'refund_amount' => $status === PaymentStatus::Refunded
                ? $amount
                : ($status === PaymentStatus::PartiallyRefunded ? (int) round($amount * 0.3) : 0),
            'notes' => $this->faker->optional()->sentence(),
            'reference_number' => $this->faker->optional()->bothify('PAY-#####'),
            'metadata' => null,
            'created_by_user_id' => User::factory(),
        ];
    }
}
