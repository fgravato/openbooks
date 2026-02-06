<?php

declare(strict_types=1);

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Enums\Role;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();
    $this->user = User::factory()->create([
        'organization_id' => $this->organization->id,
        'role' => Role::Admin,
    ]);
    $this->client = Client::factory()->create([
        'organization_id' => $this->organization->id,
    ]);
});

test('can list invoices', function (): void {
    Sanctum::actingAs($this->user);

    Invoice::factory()->count(3)->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $response = $this->getJson('/api/invoices');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('list invoices only shows invoices from current organization', function (): void {
    $otherOrg = Organization::factory()->create();
    $otherClient = Client::factory()->create(['organization_id' => $otherOrg->id]);

    Invoice::factory()->count(2)->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    Invoice::factory()->count(3)->create([
        'organization_id' => $otherOrg->id,
        'client_id' => $otherClient->id,
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/invoices');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

test('can filter invoices by status', function (): void {
    Sanctum::actingAs($this->user);

    Invoice::factory()->count(2)->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'status' => InvoiceStatus::Draft,
    ]);

    Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'status' => InvoiceStatus::Sent,
    ]);

    $response = $this->getJson('/api/invoices?status=draft');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

test('can filter invoices by client', function (): void {
    $client2 = Client::factory()->create(['organization_id' => $this->organization->id]);

    Invoice::factory()->count(2)->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $client2->id,
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->getJson("/api/invoices?client_id={$this->client->id}");

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

test('can search invoices by invoice number', function (): void {
    Sanctum::actingAs($this->user);

    Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'invoice_number' => 'INV-2024-00123',
    ]);

    Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'invoice_number' => 'INV-2024-00456',
    ]);

    $response = $this->getJson('/api/invoices?search=00123');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.invoice_number', 'INV-2024-00123');
});

test('can create invoice', function (): void {
    Sanctum::actingAs($this->user);

    $invoiceData = [
        'client_id' => $this->client->id,
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'currency_code' => 'USD',
        'notes' => 'Test invoice',
        'lines' => [
            [
                'description' => 'Service 1',
                'quantity' => 1,
                'unit_price' => 10000,
                'tax_percent' => 10,
            ],
            [
                'description' => 'Service 2',
                'quantity' => 2,
                'unit_price' => 5000,
                'tax_percent' => 10,
            ],
        ],
    ];

    $response = $this->postJson('/api/invoices', $invoiceData);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.client.id', $this->client->id);

    $this->assertDatabaseHas('invoices', [
        'client_id' => $this->client->id,
        'organization_id' => $this->organization->id,
        'status' => InvoiceStatus::Draft->value,
    ]);

    $invoice = Invoice::latest()->first();
    expect($invoice->lines)->toHaveCount(2);
});

test('cannot create invoice without required fields', function (): void {
    Sanctum::actingAs($this->user);

    $response = $this->postJson('/api/invoices', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['client_id', 'issue_date', 'due_date']);
});

test('can view single invoice', function (): void {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $invoice->id)
        ->assertJsonPath('data.client.id', $this->client->id);
});

test('cannot view invoice from another organization', function (): void {
    $otherOrg = Organization::factory()->create();
    $otherClient = Client::factory()->create(['organization_id' => $otherOrg->id]);

    $invoice = Invoice::factory()->create([
        'organization_id' => $otherOrg->id,
        'client_id' => $otherClient->id,
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertNotFound();
});

test('can update draft invoice', function (): void {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'status' => InvoiceStatus::Draft,
        'notes' => 'Original notes',
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->putJson("/api/invoices/{$invoice->id}", [
        'client_id' => $this->client->id,
        'issue_date' => $invoice->issue_date->toDateString(),
        'due_date' => $invoice->due_date->toDateString(),
        'notes' => 'Updated notes',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.notes', 'Updated notes');

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'notes' => 'Updated notes',
    ]);
});

test('cannot update sent invoice', function (): void {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'status' => InvoiceStatus::Sent,
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->putJson("/api/invoices/{$invoice->id}", [
        'client_id' => $this->client->id,
        'issue_date' => $invoice->issue_date->toDateString(),
        'due_date' => $invoice->due_date->toDateString(),
        'notes' => 'Updated notes',
    ]);

    $response->assertForbidden();
});

test('can delete draft invoice', function (): void {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'status' => InvoiceStatus::Draft,
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->deleteJson("/api/invoices/{$invoice->id}");

    $response->assertNoContent();

    $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
});

test('cannot delete sent invoice', function (): void {
    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'status' => InvoiceStatus::Sent,
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->deleteJson("/api/invoices/{$invoice->id}");

    $response->assertForbidden();
});

test('employee without permission cannot access invoices', function (): void {
    $employee = User::factory()->create([
        'organization_id' => $this->organization->id,
        'role' => Role::Employee,
    ]);

    Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    Sanctum::actingAs($employee);

    $response = $this->getJson('/api/invoices');

    $response->assertOk()
        ->assertJsonCount(0, 'data');
});

test('accountant can view but not create invoices', function (): void {
    $accountant = User::factory()->create([
        'organization_id' => $this->organization->id,
        'role' => Role::Accountant,
    ]);

    $invoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    Sanctum::actingAs($accountant);

    // Can view
    $response = $this->getJson("/api/invoices/{$invoice->id}");
    $response->assertOk();

    // Cannot create
    $response = $this->postJson('/api/invoices', [
        'client_id' => $this->client->id,
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
    ]);
    $response->assertForbidden();
});

test('unauthenticated request is rejected', function (): void {
    $response = $this->getJson('/api/invoices');

    $response->assertUnauthorized();
});

test('invoice totals are automatically calculated on creation', function (): void {
    Sanctum::actingAs($this->user);

    $invoiceData = [
        'client_id' => $this->client->id,
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'lines' => [
            [
                'description' => 'Service',
                'quantity' => 2,
                'unit_price' => 10000, // $100 each
                'tax_percent' => 10,
            ],
        ],
    ];

    $response = $this->postJson('/api/invoices', $invoiceData);

    $response->assertCreated();

    $invoice = Invoice::latest()->first();

    // Subtotal: 2 * $100 = $200
    expect($invoice->subtotal)->toBe(20000)
        // Tax: $200 * 10% = $20
        ->and($invoice->tax_amount)->toBe(2000)
        // Total: $200 + $20 = $220
        ->and($invoice->total)->toBe(22000)
        ->and($invoice->amount_outstanding)->toBe(22000);
});

test('can duplicate invoice', function (): void {
    $originalInvoice = Invoice::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
        'status' => InvoiceStatus::Paid,
        'invoice_number' => 'INV-2024-00001',
    ]);

    InvoiceLine::factory()->count(2)->create(['invoice_id' => $originalInvoice->id]);

    Sanctum::actingAs($this->user);

    $response = $this->postJson("/api/invoices/{$originalInvoice->id}/duplicate");

    $response->assertCreated()
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.client.id', $this->client->id);

    $duplicateId = $response->json('data.id');
    $duplicate = Invoice::find($duplicateId);

    expect($duplicate->invoice_number)->not->toBe($originalInvoice->invoice_number)
        ->and($duplicate->status)->toBe(InvoiceStatus::Draft)
        ->and($duplicate->lines()->count())->toBe(2);
});

test('pagination works correctly', function (): void {
    Sanctum::actingAs($this->user);

    Invoice::factory()->count(20)->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $response = $this->getJson('/api/invoices?per_page=10');

    $response->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure([
            'data',
            'links',
            'meta' => ['current_page', 'last_page', 'total'],
        ]);
});
