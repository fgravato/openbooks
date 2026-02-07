<?php

declare(strict_types=1);

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->org1 = Organization::factory()->create(['name' => 'Organization 1', 'slug' => 'org1']);
    $this->org2 = Organization::factory()->create(['name' => 'Organization 2', 'slug' => 'org2']);
});

test('organization scope automatically filters queries by authenticated user organization', function (): void {
    $user1 = User::factory()->create(['organization_id' => $this->org1->id]);
    $user2 = User::factory()->create(['organization_id' => $this->org2->id]);

    $client1 = Client::factory()->create(['organization_id' => $this->org1->id]);
    $client2 = Client::factory()->create(['organization_id' => $this->org2->id]);

    // When user 1 is authenticated, should only see org 1 clients
    Auth::login($user1);
    $clients = Client::all();
    expect($clients)->toHaveCount(1)
        ->and($clients->first()->id)->toBe($client1->id);

    // When user 2 is authenticated, should only see org 2 clients
    Auth::login($user2);
    $clients = Client::all();
    expect($clients)->toHaveCount(1)
        ->and($clients->first()->id)->toBe($client2->id);
});

test('organization scope prevents cross-organization data access', function (): void {
    $user1 = User::factory()->create(['organization_id' => $this->org1->id]);
    $client2 = Client::factory()->create(['organization_id' => $this->org2->id]);

    Auth::login($user1);

    // User from org 1 should not be able to find client from org 2
    $result = Client::find($client2->id);

    expect($result)->toBeNull();
});

test('organization scope can be bypassed with withoutGlobalScopes', function (): void {
    $user1 = User::factory()->create(['organization_id' => $this->org1->id]);

    Client::factory()->create(['organization_id' => $this->org1->id]);
    Client::factory()->create(['organization_id' => $this->org2->id]);

    Auth::login($user1);

    // With scope: should see 1 client
    expect(Client::count())->toBe(1);

    // Without scope: should see all clients
    expect(Client::withoutGlobalScopes()->count())->toBe(2);
});

test('organization scope applies to relationships', function (): void {
    $user1 = User::factory()->create(['organization_id' => $this->org1->id]);

    $client1 = Client::factory()->create(['organization_id' => $this->org1->id]);
    $client2 = Client::factory()->create(['organization_id' => $this->org2->id]);

    $invoice1 = Invoice::factory()->create([
        'organization_id' => $this->org1->id,
        'client_id' => $client1->id,
    ]);

    $invoice2 = Invoice::factory()->create([
        'organization_id' => $this->org2->id,
        'client_id' => $client2->id,
    ]);

    Auth::login($user1);

    // Should only see invoices from org 1
    $invoices = Invoice::with('client')->get();

    expect($invoices)->toHaveCount(1)
        ->and($invoices->first()->id)->toBe($invoice1->id)
        ->and($invoices->first()->client->id)->toBe($client1->id);
});

test('organization scope automatically sets organization_id on model creation', function (): void {
    $user = User::factory()->create(['organization_id' => $this->org1->id]);
    Auth::login($user);

    // Pass null to override factory's default and let the trait set it
    $client = Client::factory()->create([
        'organization_id' => null,
        'first_name' => 'Test',
        'company_name' => 'Test Client',
    ]);

    expect($client->organization_id)->toBe($this->org1->id);
});

test('organization scope does not override explicitly set organization_id', function (): void {
    $user = User::factory()->create(['organization_id' => $this->org1->id]);
    Auth::login($user);

    // Even though user is from org1, we explicitly set org2
    $client = Client::factory()->create([
        'first_name' => 'Test',
        'company_name' => 'Test Client',
        'organization_id' => $this->org2->id, // explicitly set
    ]);

    expect($client->organization_id)->toBe($this->org2->id);
});

test('organization scope handles queries with no authenticated user', function (): void {
    Client::factory()->create(['organization_id' => $this->org1->id]);
    Client::factory()->create(['organization_id' => $this->org2->id]);

    Auth::logout();

    // In console mode (testing), should return all results
    // In web mode without auth, would return empty results
    $clients = Client::all();

    // In test environment (console), scope doesn't apply the restriction
    expect($clients)->toHaveCount(2);
});

test('invoices are properly scoped to organization', function (): void {
    $user1 = User::factory()->create(['organization_id' => $this->org1->id]);
    $user2 = User::factory()->create(['organization_id' => $this->org2->id]);

    $client1 = Client::factory()->create(['organization_id' => $this->org1->id]);
    $client2 = Client::factory()->create(['organization_id' => $this->org2->id]);

    $invoice1 = Invoice::factory()->create([
        'organization_id' => $this->org1->id,
        'client_id' => $client1->id,
    ]);

    $invoice2 = Invoice::factory()->create([
        'organization_id' => $this->org2->id,
        'client_id' => $client2->id,
    ]);

    // User 1 should only see invoices from org 1
    Auth::login($user1);
    $invoices = Invoice::all();
    expect($invoices)->toHaveCount(1)
        ->and($invoices->first()->id)->toBe($invoice1->id);

    // User 2 should only see invoices from org 2
    Auth::login($user2);
    $invoices = Invoice::all();
    expect($invoices)->toHaveCount(1)
        ->and($invoices->first()->id)->toBe($invoice2->id);
});

test('organization scope does not apply to Organization model itself', function (): void {
    $user = User::factory()->create(['organization_id' => $this->org1->id]);
    Auth::login($user);

    // Organizations should not be scoped by organization_id
    // (that would be circular and prevent org management)
    $orgs = Organization::all();

    expect($orgs)->toHaveCount(2);
});

test('soft deleted records are scoped to organization', function (): void {
    $user = User::factory()->create(['organization_id' => $this->org1->id]);

    $client1 = Client::factory()->create(['organization_id' => $this->org1->id]);
    $client2 = Client::factory()->create(['organization_id' => $this->org2->id]);

    $client1->delete();
    $client2->delete();

    Auth::login($user);

    // Should only see soft-deleted clients from org 1
    $trashedClients = Client::onlyTrashed()->get();

    expect($trashedClients)->toHaveCount(1)
        ->and($trashedClients->first()->id)->toBe($client1->id);
});

test('organization scope works with complex where clauses', function (): void {
    $user = User::factory()->create(['organization_id' => $this->org1->id]);
    Auth::login($user);

    $client1 = Client::factory()->create([
        'organization_id' => $this->org1->id,
        'company_name' => 'Alpha Corp',
    ]);

    $client2 = Client::factory()->create([
        'organization_id' => $this->org1->id,
        'company_name' => 'Beta Inc',
    ]);

    $client3 = Client::factory()->create([
        'organization_id' => $this->org2->id,
        'company_name' => 'Alpha Corp', // same name but different org
    ]);

    // Search should be scoped to current org
    $results = Client::where('company_name', 'Alpha Corp')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($client1->id);
});

test('organization scope works with aggregate queries', function (): void {
    $user = User::factory()->create(['organization_id' => $this->org1->id]);
    Auth::login($user);

    Client::factory()->count(3)->create(['organization_id' => $this->org1->id]);
    Client::factory()->count(5)->create(['organization_id' => $this->org2->id]);

    // Count should be scoped to current organization
    $count = Client::count();

    expect($count)->toBe(3);
});
