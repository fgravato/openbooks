<?php

declare(strict_types=1);

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();
    $this->client = Client::factory()->create(['organization_id' => $this->organization->id]);
    $this->invoice = Invoice::factory()
        ->withoutLines()
        ->create([
            'organization_id' => $this->organization->id,
            'client_id' => $this->client->id,
        ]);
});

test('isRefundable returns true for completed payment', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 10000,
        'status' => PaymentStatus::Completed,
        'refund_amount' => 0,
    ]);

    expect($payment->isRefundable())->toBeTrue();
});

test('isRefundable returns false for pending payment', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 10000,
        'status' => PaymentStatus::Pending,
        'refund_amount' => 0,
    ]);

    expect($payment->isRefundable())->toBeFalse();
});

test('isRefundable returns false for failed payment', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 10000,
        'status' => PaymentStatus::Failed,
        'refund_amount' => 0,
    ]);

    expect($payment->isRefundable())->toBeFalse();
});

test('isRefundable returns false for fully refunded payment', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 10000,
        'status' => PaymentStatus::Refunded,
        'refund_amount' => 10000,
    ]);

    expect($payment->isRefundable())->toBeFalse();
});

test('isRefundable returns true for partially refunded payment with remaining amount', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 10000,
        'status' => PaymentStatus::PartiallyRefunded,
        'refund_amount' => 3000,
    ]);

    expect($payment->isRefundable())->toBeTrue();
});

test('getRemainingAmount returns correct amount', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 10000,
        'refund_amount' => 3000,
    ]);

    expect($payment->getRemainingAmount())->toBe(7000);
});

test('getRemainingAmount returns zero when fully refunded', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 10000,
        'refund_amount' => 10000,
    ]);

    expect($payment->getRemainingAmount())->toBe(0);
});

test('getRemainingAmount never goes negative', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 10000,
        'refund_amount' => 15000, // Somehow more than amount
    ]);

    expect($payment->getRemainingAmount())->toBe(0);
});

test('markAsCompleted sets status and paid_at', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'status' => PaymentStatus::Pending,
        'paid_at' => null,
    ]);

    $payment->markAsCompleted();

    expect($payment->status)->toBe(PaymentStatus::Completed)
        ->and($payment->paid_at)->not->toBeNull();
});

test('markAsFailed sets status and stores failure reason', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'status' => PaymentStatus::Pending,
    ]);

    $payment->markAsFailed('Insufficient funds');

    $payment->refresh();

    expect($payment->status)->toBe(PaymentStatus::Failed)
        ->and($payment->metadata)->toHaveKey('failure_reason')
        ->and($payment->metadata['failure_reason'])->toBe('Insufficient funds');
});

test('applyRefund updates refund amount and status', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 10000,
        'status' => PaymentStatus::Completed,
        'refund_amount' => 0,
        'refunded_at' => null,
    ]);

    $payment->applyRefund(3000);

    expect($payment->refund_amount)->toBe(3000)
        ->and($payment->status)->toBe(PaymentStatus::PartiallyRefunded)
        ->and($payment->refunded_at)->not->toBeNull();
});

test('applyRefund marks as fully refunded when refund equals payment', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 10000,
        'status' => PaymentStatus::Completed,
        'refund_amount' => 0,
    ]);

    $payment->applyRefund(10000);

    expect($payment->refund_amount)->toBe(10000)
        ->and($payment->status)->toBe(PaymentStatus::Refunded);
});

test('applyRefund caps refund at payment amount', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 10000,
        'status' => PaymentStatus::Completed,
        'refund_amount' => 0,
    ]);

    $payment->applyRefund(15000); // Try to refund more than payment

    expect($payment->refund_amount)->toBe(10000) // Capped at payment amount
        ->and($payment->status)->toBe(PaymentStatus::Refunded);
});

test('applyRefund accumulates multiple refunds', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 10000,
        'status' => PaymentStatus::Completed,
        'refund_amount' => 0,
    ]);

    $payment->applyRefund(3000);
    expect($payment->refund_amount)->toBe(3000);

    $payment->applyRefund(2000);
    expect($payment->refund_amount)->toBe(5000)
        ->and($payment->status)->toBe(PaymentStatus::PartiallyRefunded);
});

test('payment belongs to organization', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
    ]);

    expect($payment->organization)->not->toBeNull()
        ->and($payment->organization->id)->toBe($this->organization->id);
});

test('payment belongs to invoice', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
    ]);

    expect($payment->invoice)->not->toBeNull()
        ->and($payment->invoice->id)->toBe($this->invoice->id);
});

test('payment belongs to client', function (): void {
    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'invoice_id' => $this->invoice->id,
    ]);

    expect($payment->client)->not->toBeNull()
        ->and($payment->client->id)->toBe($this->client->id);
});

test('byStatus scope filters payments correctly', function (): void {
    Payment::factory()->count(2)->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'status' => PaymentStatus::Completed,
    ]);

    Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
        'status' => PaymentStatus::Failed,
    ]);

    $completedPayments = Payment::byStatus(PaymentStatus::Completed)->get();

    expect($completedPayments)->toHaveCount(2);
});

test('byInvoice scope filters payments correctly', function (): void {
    $invoice2 = Invoice::factory()
        ->withoutLines()
        ->create([
            'organization_id' => $this->organization->id,
            'client_id' => $this->client->id,
        ]);

    Payment::factory()->count(2)->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $this->invoice->id,
    ]);

    Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $invoice2->id,
    ]);

    $payments = Payment::byInvoice($this->invoice->id)->get();

    expect($payments)->toHaveCount(2);
});
