<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Models;

use App\Domains\Invoicing\Enums\InvoiceLineType;
use Database\Factories\Invoicing\InvoiceLineFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use HasFactory;

    protected string $table = 'invoice_lines';

    protected array $fillable = [
        'invoice_id',
        'type',
        'description',
        'quantity',
        'unit_price',
        'tax_name',
        'tax_percent',
        'amount',
        'expense_id',
        'time_entry_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => InvoiceLineType::class,
            'quantity' => 'decimal:2',
            'unit_price' => 'integer',
            'tax_percent' => 'decimal:2',
            'amount' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected static function newFactory(): Factory
    {
        return InvoiceLineFactory::new();
    }

    protected static function booted(): void
    {
        static::saving(static function (InvoiceLine $line): void {
            $line->amount = $line->calculateAmount();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo('App\\Domains\\Expenses\\Models\\Expense');
    }

    public function timeEntry(): BelongsTo
    {
        return $this->belongsTo('App\\Domains\\TimeTracking\\Models\\TimeEntry');
    }

    public function calculateAmount(): int
    {
        $quantity = (float) $this->quantity;
        $unitPrice = (int) $this->unit_price;

        return (int) round($quantity * $unitPrice);
    }

    public function getTaxAmount(): int
    {
        $taxPercent = (float) ($this->tax_percent ?? 0);

        if ($taxPercent <= 0) {
            return 0;
        }

        return (int) round($this->calculateAmount() * ($taxPercent / 100));
    }
}
