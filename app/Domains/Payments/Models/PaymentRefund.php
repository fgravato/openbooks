<?php

declare(strict_types=1);

namespace App\Domains\Payments\Models;

use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use App\Traits\BelongsToOrganization;
use Database\Factories\Payments\PaymentRefundFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRefund extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    protected $table = 'payment_refunds';

    protected $fillable = [
        'payment_id',
        'organization_id',
        'amount',
        'reason',
        'gateway_refund_id',
        'refunded_by_user_id',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'refunded_at' => 'datetime',
        ];
    }

    protected static function newFactory(): Factory
    {
        return PaymentRefundFactory::new();
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by_user_id');
    }
}
