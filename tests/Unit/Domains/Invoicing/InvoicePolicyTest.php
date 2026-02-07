<?php

declare(strict_types=1);

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Enums\Role;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Policies\InvoicePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->org1 = Organization::factory()->create(['name' => 'Org 1']);
    $this->org2 = Organization::factory()->create(['name' => 'Org 2']);
    $this->policy = new InvoicePolicy();
});

describe('view policy', function (): void {
    test('owner can view invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create(['organization_id' => $this->org1->id]);

        expect($this->policy->view($user, $invoice))->toBeTrue();
    });

    test('admin can view invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Admin,
        ]);

        $invoice = Invoice::factory()->create(['organization_id' => $this->org1->id]);

        expect($this->policy->view($user, $invoice))->toBeTrue();
    });

    test('manager can view invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Manager,
        ]);

        $invoice = Invoice::factory()->create(['organization_id' => $this->org1->id]);

        expect($this->policy->view($user, $invoice))->toBeTrue();
    });

    test('accountant can view invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Accountant,
        ]);

        $invoice = Invoice::factory()->create(['organization_id' => $this->org1->id]);

        expect($this->policy->view($user, $invoice))->toBeTrue();
    });

    test('employee cannot view invoices without permission', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Employee,
        ]);

        $invoice = Invoice::factory()->create(['organization_id' => $this->org1->id]);

        expect($this->policy->view($user, $invoice))->toBeFalse();
    });

    test('user cannot view invoices from another organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create(['organization_id' => $this->org2->id]);

        expect($this->policy->view($user, $invoice))->toBeFalse();
    });
});

describe('create policy', function (): void {
    test('owner can create invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('admin can create invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Admin,
        ]);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('manager can create invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Manager,
        ]);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('accountant cannot create invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Accountant,
        ]);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('employee cannot create invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Employee,
        ]);

        expect($this->policy->create($user))->toBeFalse();
    });
});

describe('update policy', function (): void {
    test('owner can update draft invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Draft,
        ]);

        expect($this->policy->update($user, $invoice))->toBeTrue();
    });

    test('manager can update draft invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Manager,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Draft,
        ]);

        expect($this->policy->update($user, $invoice))->toBeTrue();
    });

    test('user cannot update sent invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Sent,
        ]);

        expect($this->policy->update($user, $invoice))->toBeFalse();
    });

    test('user cannot update paid invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Paid,
        ]);

        expect($this->policy->update($user, $invoice))->toBeFalse();
    });

    test('user cannot update invoices from another organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org2->id,
            'status' => InvoiceStatus::Draft,
        ]);

        expect($this->policy->update($user, $invoice))->toBeFalse();
    });

    test('accountant cannot update invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Accountant,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Draft,
        ]);

        expect($this->policy->update($user, $invoice))->toBeFalse();
    });
});

describe('delete policy', function (): void {
    test('owner can delete draft invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Draft,
        ]);

        expect($this->policy->delete($user, $invoice))->toBeTrue();
    });

    test('manager can delete draft invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Manager,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Draft,
        ]);

        expect($this->policy->delete($user, $invoice))->toBeTrue();
    });

    test('user cannot delete sent invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Sent,
        ]);

        expect($this->policy->delete($user, $invoice))->toBeFalse();
    });

    test('user cannot delete paid invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Paid,
        ]);

        expect($this->policy->delete($user, $invoice))->toBeFalse();
    });

    test('user cannot delete invoices from another organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org2->id,
            'status' => InvoiceStatus::Draft,
        ]);

        expect($this->policy->delete($user, $invoice))->toBeFalse();
    });

    test('employee cannot delete invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Employee,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Draft,
        ]);

        expect($this->policy->delete($user, $invoice))->toBeFalse();
    });
});

describe('send policy', function (): void {
    test('owner can send draft invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Draft,
        ]);

        expect($this->policy->send($user, $invoice))->toBeTrue();
    });

    test('manager can send draft invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Manager,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Draft,
        ]);

        expect($this->policy->send($user, $invoice))->toBeTrue();
    });

    test('user cannot send already sent invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Sent,
        ]);

        expect($this->policy->send($user, $invoice))->toBeFalse();
    });

    test('user cannot send paid invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Paid,
        ]);

        expect($this->policy->send($user, $invoice))->toBeFalse();
    });

    test('user cannot send invoices from another organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org2->id,
            'status' => InvoiceStatus::Draft,
        ]);

        expect($this->policy->send($user, $invoice))->toBeFalse();
    });

    test('accountant cannot send invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Accountant,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Draft,
        ]);

        expect($this->policy->send($user, $invoice))->toBeFalse();
    });
});

describe('duplicate policy', function (): void {
    test('owner can duplicate invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Paid,
        ]);

        expect($this->policy->duplicate($user, $invoice))->toBeTrue();
    });

    test('manager can duplicate invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Manager,
        ]);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->org1->id,
            'status' => InvoiceStatus::Sent,
        ]);

        expect($this->policy->duplicate($user, $invoice))->toBeTrue();
    });

    test('accountant can duplicate invoices from their organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Accountant,
        ]);

        $invoice = Invoice::factory()->create(['organization_id' => $this->org1->id]);

        expect($this->policy->duplicate($user, $invoice))->toBeTrue();
    });

    test('user cannot duplicate invoices from another organization', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Owner,
        ]);

        $invoice = Invoice::factory()->create(['organization_id' => $this->org2->id]);

        expect($this->policy->duplicate($user, $invoice))->toBeFalse();
    });

    test('employee cannot duplicate invoices', function (): void {
        $user = User::factory()->create([
            'organization_id' => $this->org1->id,
            'role' => Role::Employee,
        ]);

        $invoice = Invoice::factory()->create(['organization_id' => $this->org1->id]);

        expect($this->policy->duplicate($user, $invoice))->toBeFalse();
    });
});
