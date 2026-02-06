<?php

declare(strict_types=1);

namespace App\Domains\Payments\Models;

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Models\Invoice;
use App\Traits\BelongsToOrganization;
use Database\Factories\Payments\CreditNoteFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNote extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'credit_notes';

    protected $fillable = [
        'organization_id',
        'client_id',
        'credit_note_number',
        'amount',
        'remaining_amount',
        'reason',
        'invoice_id',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'remaining_amount' => 'integer',
        ];
    }

    protected static function newFactory(): Factory
    {
        return CreditNoteFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function appliedTo(): HasMany
    {
        return $this->hasMany(CreditNoteApplication::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function isFullyApplied(): bool
    {
        return (int) $this->remaining_amount <= 0;
    }

    public function getRemainingAmount(): int
    {
        return max(0, (int) $this->remaining_amount);
    }

    public function applyToInvoice(Invoice $invoice, int $amount): CreditNoteApplication
    {
        $appliedAmount = max(0, min($amount, (int) $this->remaining_amount));

        $application = $this->appliedTo()->create([
            'invoice_id' => $invoice->id,
            'amount' => $appliedAmount,
            'applied_at' => \now(),
        ]);

        $this->remaining_amount = max(0, (int) $this->remaining_amount - $appliedAmount);
        $this->save();

        return $application;
    }
}
