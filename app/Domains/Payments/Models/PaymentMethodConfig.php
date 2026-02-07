<?php

declare(strict_types=1);

namespace App\Domains\Payments\Models;

use App\Domains\Clients\Models\Client;
use App\Domains\Identity\Models\Organization;
use App\Domains\Payments\Enums\PaymentGateway;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethodConfig extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'payment_method_configs';

    protected $fillable = [
        'organization_id',
        'client_id',
        'gateway',
        'gateway_payment_method_id',
        'type',
        'last_four',
        'brand',
        'exp_month',
        'exp_year',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'gateway' => PaymentGateway::class,
            'exp_month' => 'integer',
            'exp_year' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function maskNumber(): string
    {
        return '**** **** **** '.($this->last_four ?: '****');
    }
}
