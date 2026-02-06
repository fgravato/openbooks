<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Models;

use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Enums\RecurringExpenseFrequency;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Traits\BelongsToOrganization;
use Carbon\Carbon;
use Database\Factories\Expenses\RecurringExpenseFactory;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class RecurringExpense extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    protected string $table = 'recurring_expenses';

    protected array $fillable = [
        'organization_id',
        'expense_category_id',
        'vendor',
        'description',
        'estimated_amount',
        'frequency',
        'start_date',
        'end_date',
        'next_occurrence_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'estimated_amount' => 'integer',
            'frequency' => RecurringExpenseFrequency::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'next_occurrence_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): Factory
    {
        return RecurringExpenseFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function generatedExpenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function shouldGenerate(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->next_occurrence_date === null || $this->next_occurrence_date->isFuture()) {
            return false;
        }

        if ($this->start_date !== null && $this->next_occurrence_date->isBefore($this->start_date)) {
            return false;
        }

        if ($this->end_date !== null && $this->next_occurrence_date->isAfter($this->end_date)) {
            return false;
        }

        return true;
    }

    public function generateExpense(): Expense
    {
        $user = User::query()
            ->where('organization_id', $this->organization_id)
            ->oldest('id')
            ->first();

        if ($user === null) {
            throw new RuntimeException('Cannot generate recurring expense without at least one organization user.');
        }

        $expense = Expense::query()->create([
            'organization_id' => (int) $this->organization_id,
            'user_id' => (int) $user->id,
            'category_id' => (int) $this->expense_category_id,
            'recurring_expense_id' => (int) $this->id,
            'vendor' => (string) $this->vendor,
            'description' => (string) $this->description,
            'amount' => (int) $this->estimated_amount,
            'currency_code' => (string) ($this->organization->currency_code ?? 'USD'),
            'tax_name' => null,
            'tax_percent' => 0,
            'tax_amount' => 0,
            'date' => Carbon::today(),
            'status' => ExpenseStatus::Pending,
            'is_billable' => false,
            'is_reimbursable' => false,
            'markup_percent' => null,
        ]);

        $this->next_occurrence_date = Carbon::instance($this->calculateNextDate());
        $this->save();

        return $expense->fresh();
    }

    public function calculateNextDate(): DateTime
    {
        $base = Carbon::parse((string) $this->next_occurrence_date);

        $next = match ($this->frequency) {
            RecurringExpenseFrequency::Weekly => $base->copy()->addWeek(),
            RecurringExpenseFrequency::Monthly => $base->copy()->addMonth(),
            RecurringExpenseFrequency::Quarterly => $base->copy()->addMonths(3),
            RecurringExpenseFrequency::Annually => $base->copy()->addYear(),
        };

        return $next->toDateTime();
    }
}
