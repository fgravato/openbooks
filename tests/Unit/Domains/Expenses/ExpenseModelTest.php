<?php

declare(strict_types=1);

use App\Domains\Clients\Models\Client;
use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Models\Expense;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();
    $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
    $this->client = Client::factory()->create(['organization_id' => $this->organization->id]);
});

test('getTotalAmount returns amount plus tax', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'amount' => 10000, // $100
        'tax_amount' => 1000, // $10
    ]);

    expect($expense->getTotalAmount())->toBe(11000); // $110
});

test('getTotalAmount returns amount when no tax', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'amount' => 10000,
        'tax_amount' => 0,
    ]);

    expect($expense->getTotalAmount())->toBe(10000);
});

test('getBillableAmount includes markup', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'amount' => 10000, // $100
        'tax_amount' => 0,
        'markup_percent' => 20, // 20% markup
    ]);

    // $100 + 20% = $120
    expect($expense->getBillableAmount())->toBe(12000);
});

test('getBillableAmount without markup returns total', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'amount' => 10000,
        'tax_amount' => 1000,
        'markup_percent' => 0,
    ]);

    expect($expense->getBillableAmount())->toBe(11000);
});

test('getBillableAmount with tax and markup', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'amount' => 10000, // $100
        'tax_amount' => 1000, // $10
        'markup_percent' => 10, // 10% markup
    ]);

    // Total: $110
    // Markup: $110 * 10% = $11
    // Billable: $110 + $11 = $121
    expect($expense->getBillableAmount())->toBe(12100);
});

test('markAsApproved sets status and approver', function (): void {
    $approver = User::factory()->create(['organization_id' => $this->organization->id]);

    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'status' => ExpenseStatus::Pending,
        'approved_by_user_id' => null,
        'approved_at' => null,
    ]);

    $expense->markAsApproved($approver);

    expect($expense->status)->toBe(ExpenseStatus::Approved)
        ->and($expense->approved_by_user_id)->toBe($approver->id)
        ->and($expense->approved_at)->not->toBeNull();
});

test('markAsRejected sets status to rejected', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'status' => ExpenseStatus::Pending,
    ]);

    $expense->markAsRejected();

    expect($expense->status)->toBe(ExpenseStatus::Rejected);
});

test('markAsReimbursed sets status to reimbursed', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'status' => ExpenseStatus::Approved,
    ]);

    $expense->markAsReimbursed();

    expect($expense->status)->toBe(ExpenseStatus::Reimbursed);
});

test('attachToInvoice links expense to invoice and marks as billed', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'status' => ExpenseStatus::Approved,
        'invoice_id' => null,
    ]);

    $invoice = Invoice::factory()->withoutLines()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    $expense->attachToInvoice($invoice);

    expect($expense->invoice_id)->toBe($invoice->id)
        ->and($expense->status)->toBe(ExpenseStatus::Billed);
});

test('canBeEdited returns true for pending expenses', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'status' => ExpenseStatus::Pending,
    ]);

    expect($expense->canBeEdited())->toBeTrue();
});

test('canBeEdited returns false for approved expenses', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'status' => ExpenseStatus::Approved,
    ]);

    expect($expense->canBeEdited())->toBeFalse();
});

test('canBeEdited returns false for rejected expenses', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'status' => ExpenseStatus::Rejected,
    ]);

    expect($expense->canBeEdited())->toBeFalse();
});

test('expense belongs to user', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->user->id,
    ]);

    expect($expense->user)->not->toBeNull()
        ->and($expense->user->id)->toBe($this->user->id);
});

test('expense belongs to client', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    expect($expense->client)->not->toBeNull()
        ->and($expense->client->id)->toBe($this->client->id);
});

test('expense belongs to category', function (): void {
    $category = ExpenseCategory::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'category_id' => $category->id,
    ]);

    expect($expense->category)->not->toBeNull()
        ->and($expense->category->id)->toBe($category->id);
});

test('billable scope filters billable expenses', function (): void {
    Expense::factory()->count(2)->create([
        'organization_id' => $this->organization->id,
        'is_billable' => true,
    ]);

    Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'is_billable' => false,
    ]);

    $billable = Expense::billable()->get();

    expect($billable)->toHaveCount(2);
});

test('unbilled scope filters billable expenses without invoice', function (): void {
    $invoice = Invoice::factory()->withoutLines()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    Expense::factory()->count(2)->create([
        'organization_id' => $this->organization->id,
        'is_billable' => true,
        'invoice_id' => null,
    ]);

    Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'is_billable' => true,
        'invoice_id' => $invoice->id,
    ]);

    Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'is_billable' => false,
        'invoice_id' => null,
    ]);

    $unbilled = Expense::unbilled()->get();

    expect($unbilled)->toHaveCount(2);
});

test('byStatus scope filters expenses by status', function (): void {
    Expense::factory()->count(3)->create([
        'organization_id' => $this->organization->id,
        'status' => ExpenseStatus::Approved,
    ]);

    Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'status' => ExpenseStatus::Pending,
    ]);

    $approved = Expense::byStatus(ExpenseStatus::Approved)->get();

    expect($approved)->toHaveCount(3);
});

test('byClient scope filters expenses by client', function (): void {
    $client2 = Client::factory()->create(['organization_id' => $this->organization->id]);

    Expense::factory()->count(2)->create([
        'organization_id' => $this->organization->id,
        'client_id' => $this->client->id,
    ]);

    Expense::factory()->create([
        'organization_id' => $this->organization->id,
        'client_id' => $client2->id,
    ]);

    $expenses = Expense::byClient($this->client->id)->get();

    expect($expenses)->toHaveCount(2);
});

test('expense can be soft deleted', function (): void {
    $expense = Expense::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    $expense->delete();

    $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
});
