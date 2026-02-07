<?php

declare(strict_types=1);

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Invoicing\Enums\DiscountType;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLine;
use App\Domains\Invoicing\Services\InvoiceCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();
    $this->client = Client::factory()->create(['organization_id' => $this->organization->id]);
    $this->service = new InvoiceCalculationService();
});

test('calculateSubtotal returns sum of all line amounts', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create();

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 2,
        'unit_price' => 10000, // $100.00
        'tax_percent' => 0,
    ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 1,
        'unit_price' => 5000, // $50.00
        'tax_percent' => 0,
    ]);

    $subtotal = $this->service->calculateSubtotal($invoice);

    expect($subtotal)->toBe(25000); // $250.00
});

test('calculateTaxAmount returns sum of all line taxes', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create();

    // $100 at 10% tax = $10
    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 1,
        'unit_price' => 10000,
        'tax_percent' => 10,
    ]);

    // $200 at 5% tax = $10
    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 2,
        'unit_price' => 10000,
        'tax_percent' => 5,
    ]);

    $taxAmount = $this->service->calculateTaxAmount($invoice);

    expect($taxAmount)->toBe(2000); // $20.00
});

test('calculateDiscount returns zero when no discount is set', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create(['discount_type' => null, 'discount_value' => 0]);

    $discount = $this->service->calculateDiscount($invoice, 10000);

    expect($discount)->toBe(0);
});

test('calculateDiscount applies percentage discount correctly', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 1000, // 10% (stored as basis points)
        ]);

    $discount = $this->service->calculateDiscount($invoice, 10000);

    expect($discount)->toBe(1000); // 10% of $100.00 = $10.00
});

test('calculateDiscount applies fixed amount discount correctly', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'discount_type' => DiscountType::FixedAmount,
            'discount_value' => 2500, // $25.00
        ]);

    $discount = $this->service->calculateDiscount($invoice, 10000);

    expect($discount)->toBe(2500);
});

test('calculateDiscount caps discount at subtotal amount', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'discount_type' => DiscountType::FixedAmount,
            'discount_value' => 15000, // $150.00 discount
        ]);

    $discount = $this->service->calculateDiscount($invoice, 10000); // but subtotal is only $100

    expect($discount)->toBe(10000); // capped at subtotal
});

test('calculateTotal returns correct total with no tax or discount', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create(['discount_type' => null, 'discount_value' => 0]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 1,
        'unit_price' => 10000,
        'tax_percent' => 0,
    ]);

    $total = $this->service->calculateTotal($invoice);

    expect($total)->toBe(10000);
});

test('calculateTotal includes tax amount', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create(['discount_type' => null, 'discount_value' => 0]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 1,
        'unit_price' => 10000, // $100
        'tax_percent' => 10, // 10% = $10
    ]);

    $total = $this->service->calculateTotal($invoice);

    expect($total)->toBe(11000); // $110.00
});

test('calculateTotal subtracts discount from total', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'discount_type' => DiscountType::FixedAmount,
            'discount_value' => 1000, // $10 discount
        ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 1,
        'unit_price' => 10000,
        'tax_percent' => 0,
    ]);

    $total = $this->service->calculateTotal($invoice);

    expect($total)->toBe(9000); // $100 - $10 = $90
});

test('calculateTotal with complex scenario - multiple lines, tax, and discount', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 1000, // 10%
        ]);

    // Line 1: $100 at 10% tax
    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 1,
        'unit_price' => 10000,
        'tax_percent' => 10,
    ]);

    // Line 2: $50 at 5% tax
    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 1,
        'unit_price' => 5000,
        'tax_percent' => 5,
    ]);

    // Subtotal: $150
    // Taxes: $10 + $2.50 = $12.50
    // Discount: 10% of $150 = $15
    // Total: $150 + $12.50 - $15 = $147.50

    $total = $this->service->calculateTotal($invoice);

    expect($total)->toBe(14750);
});

test('recalculate updates all invoice totals correctly', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'discount_type' => DiscountType::FixedAmount,
            'discount_value' => 500, // $5 discount
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
            'amount_paid' => 0,
            'amount_outstanding' => 0,
        ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 2,
        'unit_price' => 5000, // $50 each
        'tax_percent' => 10,
    ]);

    $this->service->recalculate($invoice);

    $invoice->refresh();

    expect($invoice->subtotal)->toBe(10000) // $100
        ->and($invoice->tax_amount)->toBe(1000) // $10
        ->and($invoice->total)->toBe(10500) // $100 + $10 - $5 = $105
        ->and($invoice->amount_outstanding)->toBe(10500);
});

test('recalculate updates amount outstanding when payment has been made', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'discount_type' => null,
            'discount_value' => 0,
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
            'amount_paid' => 3000, // $30 already paid
            'amount_outstanding' => 0,
        ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 1,
        'unit_price' => 10000,
        'tax_percent' => 0,
    ]);

    $this->service->recalculate($invoice);

    $invoice->refresh();

    expect($invoice->total)->toBe(10000)
        ->and($invoice->amount_paid)->toBe(3000)
        ->and($invoice->amount_outstanding)->toBe(7000); // $100 - $30 = $70
});

test('recalculate ensures total never goes negative', function (): void {
    $invoice = Invoice::factory()->withoutLines()
        ->for($this->organization)
        ->for($this->client)
        ->create([
            'discount_type' => DiscountType::FixedAmount,
            'discount_value' => 20000, // $200 discount
        ]);

    InvoiceLine::factory()->create([
        'invoice_id' => $invoice->id,
        'quantity' => 1,
        'unit_price' => 10000, // $100 subtotal
        'tax_percent' => 0,
    ]);

    $this->service->recalculate($invoice);

    $invoice->refresh();

    expect($invoice->total)->toBe(0) // capped at 0, not negative
        ->and($invoice->amount_outstanding)->toBe(0);
});
