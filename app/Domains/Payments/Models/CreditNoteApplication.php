<?php

declare(strict_types=1);

namespace App\Domains\Payments\Models;

use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNoteApplication extends Model
{
    use HasFactory;

    protected $table = 'credit_note_applications';

    public bool $timestamps = false;

    protected $fillable = [
        'credit_note_id',
        'invoice_id',
        'amount',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'applied_at' => 'datetime',
        ];
    }

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
