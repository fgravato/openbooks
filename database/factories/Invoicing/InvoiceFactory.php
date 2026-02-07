<?php

declare(strict_types=1);

namespace Database\Factories\Invoicing;

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    private static array $sequenceByOrganization = [];

    private bool $skipLines = false;

    public function definition(): array
    {
        $issueDate = $this->faker->dateTimeBetween('-90 days', 'now');
        $dueDate = (clone $issueDate)->modify('+'.(string) $this->faker->numberBetween(7, 45).' days');
        $status = $this->weightedStatus();

        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'invoice_profile_id' => null,
            'invoice_number' => 'INV-TMP-00000',
            'status' => $status->value,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'sent_at' => in_array($status, [InvoiceStatus::Sent, InvoiceStatus::Viewed, InvoiceStatus::Partial, InvoiceStatus::Paid, InvoiceStatus::Overdue], true)
                ? $this->faker->dateTimeBetween($issueDate, 'now')
                : null,
            'viewed_at' => in_array($status, [InvoiceStatus::Viewed, InvoiceStatus::Partial, InvoiceStatus::Paid], true)
                ? $this->faker->dateTimeBetween($issueDate, 'now')
                : null,
            'paid_at' => $status === InvoiceStatus::Paid
                ? $this->faker->dateTimeBetween($issueDate, 'now')
                : null,
            'currency_code' => 'USD',
            'discount_type' => null,
            'discount_value' => 0,
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
            'amount_paid' => 0,
            'amount_outstanding' => 0,
            'notes' => $this->faker->optional()->sentence(),
            'terms' => $this->faker->optional()->sentence(),
            'template' => $this->faker->randomElement(['default', 'modern', 'classic']),
            'po_number' => $this->faker->optional()->bothify('PO-#####'),
            'reference' => $this->faker->optional()->bothify('REF-#####'),
            'footer_text' => 'Thank you for your business.',
            'created_by_user_id' => User::factory(),
            'updated_by_user_id' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Invoice $invoice): void {
            $organizationId = (int) $invoice->organization_id;
            $year = (string) $invoice->issue_date->format('Y');
            $key = $organizationId.'-'.$year;

            if (! array_key_exists($key, self::$sequenceByOrganization)) {
                $latestNumber = Invoice::query()
                    ->withoutGlobalScopes()
                    ->where('organization_id', $organizationId)
                    ->where('invoice_number', 'like', "INV-{$year}-%")
                    ->orderByDesc('id')
                    ->value('invoice_number');

                $latestSequence = 0;

                if (is_string($latestNumber)) {
                    $parts = explode('-', $latestNumber);
                    $latestSequence = (int) ($parts[2] ?? 0);
                }

                self::$sequenceByOrganization[$key] = $latestSequence;
            }

            self::$sequenceByOrganization[$key]++;
            $invoice->invoice_number = sprintf('INV-%s-%05d', $year, self::$sequenceByOrganization[$key]);

            if ($invoice->client->organization_id !== $organizationId) {
                $invoice->client->organization_id = $organizationId;
                $invoice->client->save();
            }

            // Only create lines if not skipped
            if (!$this->skipLines) {
                $lineCount = $this->faker->numberBetween(1, 5);

                InvoiceLineFactory::new()->count($lineCount)->create([
                    'invoice_id' => $invoice->id,
                ]);
            }

            $invoice->calculateTotals();

            if ($invoice->status === InvoiceStatus::Partial) {
                $invoice->amount_paid = (int) round(((int) $invoice->total) * 0.4);
                $invoice->amount_outstanding = max(0, (int) $invoice->total - (int) $invoice->amount_paid);
                $invoice->save();
            }

            if ($invoice->status === InvoiceStatus::Paid) {
                $invoice->amount_paid = (int) $invoice->total;
                $invoice->amount_outstanding = 0;
                $invoice->save();
            }

            if ($invoice->status === InvoiceStatus::Overdue) {
                $invoice->due_date = \now()->subDays($this->faker->numberBetween(3, 40))->toDateString();
                $invoice->save();
            }

            $invoice->save();
        });
    }

    public function draft(): self
    {
        return $this->state(fn (): array => ['status' => InvoiceStatus::Draft->value]);
    }

    public function withoutLines(): self
    {
        $this->skipLines = true;
        return $this;
    }

    private function weightedStatus(): InvoiceStatus
    {
        $pool = [
            InvoiceStatus::Draft,
            InvoiceStatus::Draft,
            InvoiceStatus::Draft,
            InvoiceStatus::Sent,
            InvoiceStatus::Sent,
            InvoiceStatus::Viewed,
            InvoiceStatus::Partial,
            InvoiceStatus::Paid,
            InvoiceStatus::Paid,
            InvoiceStatus::Overdue,
            InvoiceStatus::Cancelled,
        ];

        return $this->faker->randomElement($pool);
    }
}
