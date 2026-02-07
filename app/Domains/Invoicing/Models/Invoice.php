<?php

declare(strict_types=1);

namespace App\Domains\Invoicing\Models;

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Enums\DiscountType;
use App\Domains\Invoicing\Enums\InvoiceStatus;
use App\Domains\Invoicing\Services\InvoiceCalculationService;
use App\Domains\Invoicing\Services\InvoiceNumberService;
use App\Domains\Invoicing\Services\InvoicePdfService;
use App\Traits\BelongsToOrganization;
use Carbon\Carbon;
use Database\Factories\Invoicing\InvoiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

class Invoice extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'organization_id',
        'client_id',
        'invoice_profile_id',
        'invoice_number',
        'status',
        'issue_date',
        'due_date',
        'sent_at',
        'viewed_at',
        'paid_at',
        'currency_code',
        'discount_type',
        'discount_value',
        'subtotal',
        'tax_amount',
        'total',
        'amount_paid',
        'amount_outstanding',
        'notes',
        'terms',
        'template',
        'po_number',
        'reference',
        'footer_text',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'discount_type' => DiscountType::class,
            'issue_date' => 'date',
            'due_date' => 'date',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'paid_at' => 'datetime',
            'discount_value' => 'integer',
            'subtotal' => 'integer',
            'tax_amount' => 'integer',
            'total' => 'integer',
            'amount_paid' => 'integer',
            'amount_outstanding' => 'integer',
        ];
    }

    protected static function newFactory(): Factory
    {
        return InvoiceFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany('App\\Domains\\Payments\\Models\\Payment');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function recurringProfile(): BelongsTo
    {
        return $this->belongsTo(InvoiceProfile::class, 'invoice_profile_id');
    }

    public function scopeForOrganization(Builder $query, Organization $organization): Builder
    {
        return $query->where('organization_id', $organization->id);
    }

    public function scopeByStatus(Builder $query, InvoiceStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->whereDate('due_date', '<', Carbon::today())
            ->whereNotIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::Cancelled->value]);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->whereIn('status', [
            InvoiceStatus::Sent->value,
            InvoiceStatus::Viewed->value,
            InvoiceStatus::Partial->value,
        ]);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', InvoiceStatus::Draft->value);
    }

    public function calculateTotals(): void
    {
        app(InvoiceCalculationService::class)->recalculate($this);
    }

    public function applyPayment(\App\Domains\Payments\Models\Payment $payment): void
    {
        $paymentAmount = (int) $payment->getAttribute('amount');

        $this->amount_paid = max(0, (int) $this->amount_paid + $paymentAmount);
        $this->amount_outstanding = max(0, (int) $this->total - (int) $this->amount_paid);

        if ($this->amount_outstanding === 0) {
            $this->markAsPaid();
        } elseif (! in_array($this->status, [InvoiceStatus::Cancelled, InvoiceStatus::Paid], true)) {
            $this->status = InvoiceStatus::Partial;
        }

        $this->save();
    }

    public function markAsSent(): void
    {
        if (! $this->status->canTransitionTo(InvoiceStatus::Sent)) {
            return;
        }

        $this->status = InvoiceStatus::Sent;
        $this->sent_at = \now();
        $this->save();
    }

    public function markAsViewed(): void
    {
        if ($this->status !== InvoiceStatus::Sent) {
            return;
        }

        $this->status = InvoiceStatus::Viewed;
        $this->viewed_at = \now();
        $this->save();
    }

    public function markAsPaid(): void
    {
        if ((int) $this->amount_outstanding > 0) {
            return;
        }

        if (! in_array($this->status, [InvoiceStatus::Cancelled, InvoiceStatus::Paid], true)) {
            $this->status = InvoiceStatus::Paid;
            $this->paid_at = \now();
            $this->save();
        }
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && ! in_array($this->status, [InvoiceStatus::Paid, InvoiceStatus::Cancelled], true);
    }

    public function generatePdf(): string
    {
        return app(InvoicePdfService::class)->generate($this);
    }

    public function duplicate(): Invoice
    {
        $copy = $this->replicate([
            'invoice_number',
            'status',
            'sent_at',
            'viewed_at',
            'paid_at',
            'amount_paid',
            'amount_outstanding',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $copy->status = InvoiceStatus::Draft;
        $copy->invoice_number = app(InvoiceNumberService::class)->generateNextNumber($this->organization);
        $copy->issue_date = \now()->toDateString();
        $copy->due_date = \now()->addDays(30)->toDateString();
        $copy->amount_paid = 0;
        $copy->amount_outstanding = 0;
        $copy->save();

        $this->lines()->get()->each(function (InvoiceLine $line, int $index) use ($copy): void {
            $lineCopy = $line->replicate(['created_at', 'updated_at']);
            $lineCopy->invoice_id = $copy->id;
            $lineCopy->sort_order = $index + 1;
            $lineCopy->save();
        });

        $copy->calculateTotals();

        return $copy;
    }

    public function canBeEdited(): bool
    {
        return $this->status === InvoiceStatus::Draft;
    }

    public function getPdfUrl(): string
    {
        return URL::temporarySignedRoute(
            'invoices.pdf.download',
            \now()->addMinutes(30),
            ['invoice' => $this->id],
        );
    }
}
