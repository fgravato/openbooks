<?php

declare(strict_types=1);

namespace Database\Seeders\Payments;

use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Payments\Enums\PaymentGateway;
use App\Domains\Payments\Enums\PaymentMethod;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        Organization::query()->each(function (Organization $organization): void {
            $users = User::query()->where('organization_id', $organization->id)->pluck('id');

            if ($users->isEmpty()) {
                return;
            }

            Invoice::query()
                ->where('organization_id', $organization->id)
                ->whereIn('status', [
                    InvoiceStatus::Sent->value,
                    InvoiceStatus::Viewed->value,
                    InvoiceStatus::Partial->value,
                    InvoiceStatus::Paid->value,
                ])
                ->inRandomOrder()
                ->limit(25)
                ->get()
                ->each(function (Invoice $invoice) use ($organization, $users): void {
                    $method = \fake()->randomElement(PaymentMethod::cases());
                    $status = \fake()->randomElement([
                        PaymentStatus::Completed,
                        PaymentStatus::Completed,
                        PaymentStatus::Pending,
                        PaymentStatus::Failed,
                        PaymentStatus::PartiallyRefunded,
                    ]);
                    $amount = (int) min($invoice->amount_outstanding, \fake()->numberBetween(1000, max(1000, (int) $invoice->total)));
                    $gateway = $method->isOnline() ? PaymentGateway::Stripe : PaymentGateway::Manual;

                    $payment = Payment::query()->create([
                        'organization_id' => $organization->id,
                        'client_id' => $invoice->client_id,
                        'invoice_id' => $invoice->id,
                        'amount' => max(1000, $amount),
                        'currency_code' => $invoice->currency_code,
                        'method' => $method,
                        'status' => $status,
                        'gateway' => $gateway,
                        'gateway_transaction_id' => $gateway === PaymentGateway::Stripe
                            ? 'pi_seed_'.\fake()->unique()->numerify('##########')
                            : null,
                        'gateway_fee_amount' => $gateway === PaymentGateway::Stripe ? (int) round(max(1000, $amount) * 0.029) + 30 : 0,
                        'net_amount' => $gateway === PaymentGateway::Stripe
                            ? max(0, max(1000, $amount) - ((int) round(max(1000, $amount) * 0.029) + 30))
                            : max(1000, $amount),
                        'paid_at' => $status->isSuccessful() ? \now()->subDays(random_int(0, 45)) : null,
                        'refunded_at' => $status === PaymentStatus::PartiallyRefunded ? \now()->subDays(random_int(0, 30)) : null,
                        'refund_amount' => $status === PaymentStatus::PartiallyRefunded ? (int) round(max(1000, $amount) * 0.25) : 0,
                        'notes' => \fake()->optional()->sentence(),
                        'metadata' => null,
                        'created_by_user_id' => $users->random(),
                    ]);

                    if ($status->isSuccessful()) {
                        $invoice->applyPayment($payment);
                    }
                });
        });
    }
}
