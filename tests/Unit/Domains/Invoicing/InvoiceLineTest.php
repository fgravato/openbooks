<?php

declare(strict_types=1);

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();
    $this->client = Client::factory()->create(['organization_id' => $this->organization->id]);
    $this->invoice = Invoice::factory()
        ->for($this->organization)
        ->for($this->client)
        ->create();
});

test('calculateAmount returns correct amount for single item', function (): void {
    $line = InvoiceLine::factory()->make([
        'quantity' => 1,
        'unit_price' => 10000, // $100.00
    ]);

    $amount = $line->calculateAmount();

    expect($amount)->toBe(10000);
});

test('calculateAmount returns correct amount for multiple quantities', function (): void {
    $line = InvoiceLine::factory()->make([
        'quantity' => 3,
        'unit_price' => 2500, // $25.00 each
    ]);

    $amount = $line->calculateAmount();

    expect($amount)->toBe(7500); // $75.00
});

test('calculateAmount handles fractional quantities', function (): void {
    $line = InvoiceLine::factory()->make([
        'quantity' => 2.5, // 2.5 hours
        'unit_price' => 10000, // $100/hour
    ]);

    $amount = $line->calculateAmount();

    expect($amount)->toBe(25000); // $250.00
});

test('calculateAmount rounds to nearest integer', function (): void {
    $line = InvoiceLine::factory()->make([
        'quantity' => 1.33,
        'unit_price' => 100,
    ]);

    $amount = $line->calculateAmount();

    expect($amount)->toBe(133);
});

test('getTaxAmount calculates tax correctly', function (): void {
    $line = InvoiceLine::factory()->make([
        'quantity' => 1,
        'unit_price' => 10000, // $100.00
        'tax_percent' => 10, // 10%
    ]);

    $taxAmount = $line->getTaxAmount();

    expect($taxAmount)->toBe(1000); // $10.00
});

test('getTaxAmount returns zero when tax_percent is null', function (): void {
    $line = InvoiceLine::factory()->make([
        'quantity' => 1,
        'unit_price' => 10000,
        'tax_percent' => null,
    ]);

    $taxAmount = $line->getTaxAmount();

    expect($taxAmount)->toBe(0);
});

test('getTaxAmount returns zero when tax_percent is zero', function (): void {
    $line = InvoiceLine::factory()->make([
        'quantity' => 1,
        'unit_price' => 10000,
        'tax_percent' => 0,
    ]);

    $taxAmount = $line->getTaxAmount();

    expect($taxAmount)->toBe(0);
});

test('getTaxAmount calculates complex tax scenarios correctly', function (): void {
    $line = InvoiceLine::factory()->make([
        'quantity' => 2.5,
        'unit_price' => 10000, // $100/unit
        'tax_percent' => 8.25, // 8.25% tax
    ]);

    // Amount: 2.5 * $100 = $250
    // Tax: $250 * 8.25% = $20.625
    $taxAmount = $line->getTaxAmount();

    expect($taxAmount)->toBe(2063); // $20.63 (rounded)
});

test('amount is automatically calculated on save', function (): void {
    $line = InvoiceLine::factory()->create([
        'invoice_id' => $this->invoice->id,
        'quantity' => 3,
        'unit_price' => 5000,
        'amount' => 0, // Start with 0
    ]);

    expect($line->amount)->toBe(15000); // Automatically calculated
});

test('amount is recalculated on update', function (): void {
    $line = InvoiceLine::factory()->create([
        'invoice_id' => $this->invoice->id,
        'quantity' => 1,
        'unit_price' => 5000,
    ]);

    expect($line->amount)->toBe(5000);

    // Update quantity
    $line->quantity = 4;
    $line->save();

    expect($line->amount)->toBe(20000); // Recalculated
});

test('complex invoice line with quantity, price, and tax', function (): void {
    $line = InvoiceLine::factory()->create([
        'invoice_id' => $this->invoice->id,
        'description' => 'Consulting Services',
        'quantity' => 8.5, // hours
        'unit_price' => 15000, // $150/hour
        'tax_percent' => 10, // 10% tax
    ]);

    // Amount: 8.5 * $150 = $1,275
    expect($line->amount)->toBe(127500);

    // Tax: $1,275 * 10% = $127.50
    expect($line->getTaxAmount())->toBe(12750);
});

test('invoice line with zero quantity has zero amount', function (): void {
    $line = InvoiceLine::factory()->make([
        'quantity' => 0,
        'unit_price' => 10000,
    ]);

    expect($line->calculateAmount())->toBe(0);
});

test('invoice line with zero unit price has zero amount', function (): void {
    $line = InvoiceLine::factory()->make([
        'quantity' => 5,
        'unit_price' => 0,
    ]);

    expect($line->calculateAmount())->toBe(0);
});

test('large quantity calculations are handled correctly', function (): void {
    $line = InvoiceLine::factory()->make([
        'quantity' => 1000,
        'unit_price' => 500, // $5.00 each
    ]);

    $amount = $line->calculateAmount();

    expect($amount)->toBe(500000); // $5,000.00
});

test('invoice line belongs to invoice', function (): void {
    $line = InvoiceLine::factory()->create([
        'invoice_id' => $this->invoice->id,
    ]);

    expect($line->invoice)->not->toBeNull()
        ->and($line->invoice->id)->toBe($this->invoice->id);
});

test('tax calculations are precise with fractional percentages', function (): void {
    $line = InvoiceLine::factory()->make([
        'quantity' => 1,
        'unit_price' => 10000,
        'tax_percent' => 8.875, // NYC sales tax rate
    ]);

    $taxAmount = $line->getTaxAmount();

    // $100 * 8.875% = $8.875 = $8.88 rounded
    expect($taxAmount)->toBe(888);
});
