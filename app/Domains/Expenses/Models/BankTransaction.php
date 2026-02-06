<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Models;

use App\Domains\Identity\Models\Organization;
use App\Traits\BelongsToOrganization;
use Database\Factories\Expenses\BankTransactionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    protected string $table = 'bank_transactions';

    protected array $fillable = [
        'organization_id',
        'bank_connection_id',
        'transaction_id',
        'amount',
        'currency_code',
        'date',
        'name',
        'merchant_name',
        'category',
        'pending',
        'expense_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'date' => 'date',
            'category' => 'array',
            'pending' => 'boolean',
        ];
    }

    protected static function newFactory(): Factory
    {
        return BankTransactionFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function bankConnection(): BelongsTo
    {
        return $this->belongsTo(BankConnection::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function scopeUnlinked(Builder $query): Builder
    {
        return $query->whereNull('expense_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('pending', true);
    }

    public function isExpense(): bool
    {
        return (int) $this->amount < 0;
    }

    public function getAbsoluteAmount(): int
    {
        return abs((int) $this->amount);
    }
}
