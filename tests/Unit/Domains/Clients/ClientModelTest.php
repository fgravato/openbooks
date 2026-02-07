<?php

declare(strict_types=1);

use App\Domains\Clients\Models\Client;
use App\Domains\Clients\Models\Contact;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();
    $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
});

test('client can be created with basic information', function (): void {
    $client = Client::factory()->create([
        'organization_id' => $this->organization->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'company_name' => 'Acme Corp',
        'email' => 'john@acme.com',
    ]);

    expect($client->first_name)->toBe('John')
        ->and($client->last_name)->toBe('Doe')
        ->and($client->company_name)->toBe('Acme Corp')
        ->and($client->email)->toBe('john@acme.com');
});

test('client belongs to organization', function (): void {
    $client = Client::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    expect($client->organization)->not->toBeNull()
        ->and($client->organization->id)->toBe($this->organization->id);
});

test('client can have multiple contacts', function (): void {
    $client = Client::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    Contact::factory()->count(3)->create([
        'client_id' => $client->id,
    ]);

    expect($client->contacts)->toHaveCount(3);
});

test('client can have multiple invoices', function (): void {
    $client = Client::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    Invoice::factory()->withoutLines()->count(5)->create([
        'organization_id' => $this->organization->id,
        'client_id' => $client->id,
    ]);

    expect($client->invoices)->toHaveCount(5);
});

test('client address is stored as array', function (): void {
    $client = Client::factory()->create([
        'organization_id' => $this->organization->id,
        'address' => [
            'street' => '123 Main St',
            'city' => 'San Francisco',
            'state' => 'CA',
            'postal_code' => '94105',
            'country' => 'US',
        ],
    ]);

    expect($client->address)->toBeArray()
        ->and($client->address['street'])->toBe('123 Main St')
        ->and($client->address['city'])->toBe('San Francisco');
});

test('clients are scoped to organization', function (): void {
    $org2 = Organization::factory()->create();

    Client::factory()->count(3)->create([
        'organization_id' => $this->organization->id,
    ]);

    Client::factory()->count(2)->create([
        'organization_id' => $org2->id,
    ]);

    Auth::login($this->user);

    $clients = Client::all();

    expect($clients)->toHaveCount(3);
});

test('client can be soft deleted', function (): void {
    $client = Client::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    $client->delete();

    $this->assertSoftDeleted('clients', ['id' => $client->id]);
});

test('soft deleted clients can be restored', function (): void {
    $client = Client::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    $client->delete();
    $client->restore();

    expect($client->deleted_at)->toBeNull();
    $this->assertDatabaseHas('clients', ['id' => $client->id, 'deleted_at' => null]);
});

test('client payment terms are stored correctly', function (): void {
    $client = Client::factory()->create([
        'organization_id' => $this->organization->id,
        'payment_terms' => 30, // Net 30
    ]);

    expect($client->payment_terms)->toBe(30);
});

test('client currency code defaults correctly', function (): void {
    $client = Client::factory()->create([
        'organization_id' => $this->organization->id,
        'currency_code' => 'EUR',
    ]);

    expect($client->currency_code)->toBe('EUR');
});
