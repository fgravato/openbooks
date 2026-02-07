<?php

declare(strict_types=1);

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLine;
use App\Domains\Payments\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();
    $this->client = Client::factory()->create(['organization_id' => $this->organization->id]);
});

test('applyPayment updates amount paid and outstanding', function (): void {
    $invoice = Invoice::factory()
        ->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create(['status' => InvoiceStatus::Sent]);

    // Set specific test values after factory processing
    $invoice->update([
        'total' => 10000,
        'amount_paid' => 0,
        'amount_outstanding' => 10000,
    ]);

    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $invoice->id,
        'amount' => 3000,
    ]);

    $invoice->applyPayment($payment);

    expect($invoice->amount_paid)->toBe(3000)
        ->and($invoice->amount_outstanding)->toBe(7000)
        ->and($invoice->status)->toBe(InvoiceStatus::Partial);
});

test('applyPayment marks invoice as paid when fully paid', function (): void {
    $invoice = Invoice::factory()
        ->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create(['status' => InvoiceStatus::Sent]);

    $invoice->update([
        'total' => 10000,
        'amount_paid' => 0,
        'amount_outstanding' => 10000,
    ]);

    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $invoice->id,
        'amount' => 10000,
    ]);

    $invoice->applyPayment($payment);

    expect($invoice->amount_paid)->toBe(10000)
        ->and($invoice->amount_outstanding)->toBe(0)
        ->and($invoice->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->paid_at)->not->toBeNull();
});

test('applyPayment sets status to partial when partially paid', function (): void {
    $invoice = Invoice::factory()
        ->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create(['status' => InvoiceStatus::Sent]);

    $invoice->update([
        'total' => 10000,
        'amount_paid' => 5000,
        'amount_outstanding' => 5000,
    ]);

    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $invoice->id,
        'amount' => 2000,
    ]);

    $invoice->applyPayment($payment);

    expect($invoice->status)->toBe(InvoiceStatus::Partial)
        ->and($invoice->amount_paid)->toBe(7000)
        ->and($invoice->amount_outstanding)->toBe(3000);
});

test('applyPayment does not change status of cancelled invoice', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'total' => 10000,
            'amount_paid' => 0,
            'amount_outstanding' => 10000,
            'status' => InvoiceStatus::Cancelled,
        ]);

    $payment = Payment::factory()->create([
        'organization_id' => $this->organization->id,
        'invoice_id' => $invoice->id,
        'amount' => 5000,
    ]);

    $invoice->applyPayment($payment);

    expect($invoice->status)->toBe(InvoiceStatus::Cancelled)
        ->and($invoice->amount_paid)->toBe(5000);
});

test('markAsSent transitions from draft to sent', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create(['status' => InvoiceStatus::Draft]);

    $invoice->markAsSent();

    expect($invoice->status)->toBe(InvoiceStatus::Sent)
        ->and($invoice->sent_at)->not->toBeNull();
});

test('markAsSent does not transition from invalid status', function (): void {
    $invoice = Invoice::factory()
        ->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create(['status' => InvoiceStatus::Paid]);

    $originalSentAt = $invoice->sent_at?->toISOString();
    $invoice->markAsSent();

    expect($invoice->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->sent_at?->toISOString())->toBe($originalSentAt);
});

test('markAsViewed transitions from sent to viewed', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create(['status' => InvoiceStatus::Sent]);

    $invoice->markAsViewed();

    expect($invoice->status)->toBe(InvoiceStatus::Viewed)
        ->and($invoice->viewed_at)->not->toBeNull();
});

test('markAsViewed does not transition from non-sent status', function (): void {
    $invoice = Invoice::factory()
        ->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create(['status' => InvoiceStatus::Draft, 'viewed_at' => null]);

    $invoice->markAsViewed();

    expect($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and($invoice->viewed_at)->toBeNull();
});

test('markAsPaid sets status to paid when outstanding is zero', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'status' => InvoiceStatus::Sent,
            'total' => 10000,
            'amount_paid' => 10000,
            'amount_outstanding' => 0,
        ]);

    $invoice->markAsPaid();

    expect($invoice->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->paid_at)->not->toBeNull();
});

test('markAsPaid does not mark as paid when amount outstanding', function (): void {
    $invoice = Invoice::factory()
        ->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create(['status' => InvoiceStatus::Sent]);

    $invoice->update([
        'total' => 10000,
        'amount_paid' => 5000,
        'amount_outstanding' => 5000,
    ]);

    $invoice->markAsPaid();

    expect($invoice->status)->toBe(InvoiceStatus::Sent)
        ->and($invoice->paid_at)->toBeNull();
});

test('isOverdue returns true for past due unpaid invoice', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'status' => InvoiceStatus::Sent,
            'due_date' => now()->subDays(5),
        ]);

    expect($invoice->isOverdue())->toBeTrue();
});

test('isOverdue returns false for paid invoice', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'status' => InvoiceStatus::Paid,
            'due_date' => now()->subDays(5),
        ]);

    expect($invoice->isOverdue())->toBeFalse();
});

test('isOverdue returns false for cancelled invoice', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'status' => InvoiceStatus::Cancelled,
            'due_date' => now()->subDays(5),
        ]);

    expect($invoice->isOverdue())->toBeFalse();
});

test('isOverdue returns false when due date is in future', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'status' => InvoiceStatus::Sent,
            'due_date' => now()->addDays(5),
        ]);

    expect($invoice->isOverdue())->toBeFalse();
});

test('duplicate creates new invoice with draft status', function (): void {
    $originalInvoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'status' => InvoiceStatus::Paid,
            'invoice_number' => 'INV-2024-00001',
        ]);

    InvoiceLine::factory()->count(3)->create(['invoice_id' => $originalInvoice->id]);

    $duplicate = $originalInvoice->duplicate();

    expect($duplicate->id)->not->toBe($originalInvoice->id)
        ->and($duplicate->status)->toBe(InvoiceStatus::Draft)
        ->and($duplicate->invoice_number)->not->toBe($originalInvoice->invoice_number)
        ->and($duplicate->client_id)->toBe($originalInvoice->client_id)
        ->and($duplicate->organization_id)->toBe($originalInvoice->organization_id)
        ->and($duplicate->sent_at)->toBeNull()
        ->and($duplicate->viewed_at)->toBeNull()
        ->and($duplicate->paid_at)->toBeNull()
        ->and($duplicate->amount_paid)->toBe(0)
        ->and($duplicate->lines()->count())->toBe(3);
});

test('duplicate copies all invoice lines', function (): void {
    $originalInvoice = Invoice::factory()
        ->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create();

    InvoiceLine::factory()->create([
        'invoice_id' => $originalInvoice->id,
        'description' => 'Line 1',
        'quantity' => 2,
        'unit_price' => 5000,
        'sort_order' => 1,
    ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $originalInvoice->id,
        'description' => 'Line 2',
        'quantity' => 1,
        'unit_price' => 10000,
        'sort_order' => 2,
    ]);

    $duplicate = $originalInvoice->duplicate();

    expect($duplicate->lines()->count())->toBe(2);

    $lines = $duplicate->lines()->orderBy('sort_order')->get();

    expect($lines[0]->description)->toBe('Line 1')
        ->and($lines[0]->quantity)->toBe('2.00')
        ->and($lines[0]->unit_price)->toBe(5000)
        ->and($lines[1]->description)->toBe('Line 2');
});

test('duplicate recalculates totals', function (): void {
    $originalInvoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create();

    InvoiceLine::factory()->create([
        'invoice_id' => $originalInvoice->id,
        'quantity' => 2,
        'unit_price' => 5000,
        'tax_percent' => 10,
    ]);

    $originalInvoice->calculateTotals();
    $originalInvoice->refresh();

    $duplicate = $originalInvoice->duplicate();

    expect($duplicate->subtotal)->toBe($originalInvoice->subtotal)
        ->and($duplicate->tax_amount)->toBe($originalInvoice->tax_amount)
        ->and($duplicate->total)->toBe($originalInvoice->total);
});

test('canBeEdited returns true for draft invoices', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create(['status' => InvoiceStatus::Draft]);

    expect($invoice->canBeEdited())->toBeTrue();
});

test('canBeEdited returns false for sent invoices', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create(['status' => InvoiceStatus::Sent]);

    expect($invoice->canBeEdited())->toBeFalse();
});

test('canBeEdited returns false for paid invoices', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create(['status' => InvoiceStatus::Paid]);

    expect($invoice->canBeEdited())->toBeFalse();
});

test('calculateTotals method calls InvoiceCalculationService', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create(['subtotal' => 0, 'tax_amount' => 0, 'total' => 0]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 1,
        'unit_price' => 10000,
        'tax_percent' => 10,
    ]);

    $invoice->calculateTotals();
    $invoice->refresh();

    expect($invoice->subtotal)->toBe(10000)
        ->and($invoice->tax_amount)->toBe(1000)
        ->and($invoice->total)->toBe(11000);
});

test('invoice lines are ordered by sort_order', function (): void {
    $invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create();

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'description' => 'Third',
        'sort_order' => 3,
    ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'description' => 'First',
        'sort_order' => 1,
    ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'description' => 'Second',
        'sort_order' => 2,
    ]);

    $lines = $invoice->lines()->get();

    expect($lines[0]->description)->toBe('First')
        ->and($lines[1]->description)->toBe('Second')
        ->and($lines[2]->description)->toBe('Third');
});
