<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Models;

use App\Domains\Clients\Models\Client;
use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Models\Invoice;
use App\Traits\BelongsToOrganization;
use Database\Factories\Expenses\ExpenseFactory;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use SoftDeletes;

    protected string $table = 'expenses';

    protected array $fillable = [
        'organization_id',
        'user_id',
        'client_id',
        'project_id',
        'category_id',
        'recurring_expense_id',
        'vendor',
        'description',
        'amount',
        'currency_code',
        'tax_name',
        'tax_percent',
        'tax_amount',
        'date',
        'receipt_path',
        'status',
        'is_billable',
        'is_reimbursable',
        'markup_percent',
        'invoice_id',
        'bank_transaction_id',
        'notes',
        'approved_by_user_id',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExpenseStatus::class,
            'amount' => 'integer',
            'tax_percent' => 'decimal:2',
            'tax_amount' => 'integer',
            'date' => 'date',
            'is_billable' => 'boolean',
            'is_reimbursable' => 'boolean',
            'markup_percent' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    protected static function newFactory(): Factory
    {
        return ExpenseFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo('App\\Domains\\Projects\\Models\\Project');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    public function recurringExpense(): BelongsTo
    {
        return $this->belongsTo(RecurringExpense::class);
    }

    public function scopeForOrganization(Builder $query, Organization $organization): Builder
    {
        return $query->where('organization_id', $organization->id);
    }

    public function scopeByStatus(Builder $query, ExpenseStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeBillable(Builder $query): Builder
    {
        return $query->where('is_billable', true);
    }

    public function scopeUnbilled(Builder $query): Builder
    {
        return $query->where('is_billable', true)->whereNull('invoice_id');
    }

    public function scopeByDateRange(Builder $query, DateTime $start, DateTime $end): Builder
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function getTotalAmount(): int
    {
        return (int) $this->amount + (int) $this->tax_amount;
    }

    public function getBillableAmount(): int
    {
        $base = $this->getTotalAmount();
        $markupPercent = (float) ($this->markup_percent ?? 0);
        $markup = $markupPercent <= 0 ? 0 : (int) round($base * ($markupPercent / 100));

        return $base + $markup;
    }

    public function markAsApproved(User $approver): void
    {
        if (! $this->status->canTransitionTo(ExpenseStatus::Approved)) {
            return;
        }

        $this->status = ExpenseStatus::Approved;
        $this->approved_by_user_id = (int) $approver->id;
        $this->approved_at = now();
        $this->save();
    }

    public function markAsRejected(): void
    {
        if (! $this->status->canTransitionTo(ExpenseStatus::Rejected)) {
            return;
        }

        $this->status = ExpenseStatus::Rejected;
        $this->save();
    }

    public function markAsReimbursed(): void
    {
        if (! $this->status->canTransitionTo(ExpenseStatus::Reimbursed)) {
            return;
        }

        $this->status = ExpenseStatus::Reimbursed;
        $this->save();
    }

    public function attachToInvoice(Invoice $invoice): void
    {
        $this->invoice_id = (int) $invoice->id;
        $this->status = ExpenseStatus::Billed;
        $this->save();
    }

    public function canBeEdited(): bool
    {
        return $this->status === ExpenseStatus::Pending;
    }
}
