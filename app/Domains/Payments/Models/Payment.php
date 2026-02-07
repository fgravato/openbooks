<?php

declare(strict_types=1);

namespace App\Domains\Payments\Models;

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Payments\Enums\PaymentGateway;
use App\Domains\Payments\Enums\PaymentMethod;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Traits\BelongsToOrganization;
use Database\Factories\Payments\PaymentFactory;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'payments';

    protected $fillable = [
        'organization_id',
        'client_id',
        'invoice_id',
        'amount',
        'currency_code',
        'method',
        'status',
        'gateway',
        'gateway_transaction_id',
        'gateway_fee_amount',
        'net_amount',
        'paid_at',
        'refunded_at',
        'refund_amount',
        'notes',
        'reference_number',
        'metadata',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'gateway' => PaymentGateway::class,
            'amount' => 'integer',
            'gateway_fee_amount' => 'integer',
            'net_amount' => 'integer',
            'refund_amount' => 'integer',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function newFactory(): Factory
    {
        return PaymentFactory::new();
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

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeForOrganization(Builder $query, Organization $org): Builder
    {
        return $query->where('organization_id', $org->id);
    }

    public function scopeByStatus(Builder $query, PaymentStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('gateway', '!=', PaymentGateway::Manual->value);
    }

    public function scopeByClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByInvoice(Builder $query, int $invoiceId): Builder
    {
        return $query->where('invoice_id', $invoiceId);
    }

    public function scopeByDateRange(Builder $query, DateTime $start, DateTime $end): Builder
    {
        return $query->whereBetween('paid_at', [$start, $end]);
    }

    public function isRefundable(): bool
    {
        return $this->status === PaymentStatus::Completed
            || ($this->status === PaymentStatus::PartiallyRefunded && $this->getRemainingAmount() > 0);
    }

    public function getRemainingAmount(): int
    {
        return max(0, (int) $this->amount - (int) $this->refund_amount);
    }

    public function markAsCompleted(): void
    {
        $this->status = PaymentStatus::Completed;
        $this->paid_at = \now();
        $this->save();
    }

    public function markAsFailed(string $reason): void
    {
        $this->status = PaymentStatus::Failed;
        $metadata = (array) ($this->metadata ?? []);
        $metadata['failure_reason'] = $reason;
        $this->metadata = $metadata;
        $this->save();
    }

    public function applyRefund(int $amount): void
    {
        $newRefundAmount = max(0, (int) $this->refund_amount + $amount);
        $this->refund_amount = min((int) $this->amount, $newRefundAmount);
        $this->refunded_at = \now();
        $this->status = $this->refund_amount >= (int) $this->amount
            ? PaymentStatus::Refunded
            : PaymentStatus::PartiallyRefunded;
        $this->save();
    }
}
