<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Models;

use App\Domains\Expenses\Enums\BankAccountType;
use App\Domains\Identity\Models\Organization;
use App\Traits\BelongsToOrganization;
use Carbon\Carbon;
use Database\Factories\Expenses\BankConnectionFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankConnection extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    protected $table = 'bank_connections';

    protected $fillable = [
        'organization_id',
        'name',
        'institution_name',
        'institution_id',
        'access_token',
        'item_id',
        'account_mask',
        'account_type',
        'balance_current',
        'balance_available',
        'currency_code',
        'last_sync_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'account_type' => BankAccountType::class,
            'balance_current' => 'integer',
            'balance_available' => 'integer',
            'last_sync_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): Factory
    {
        return BankConnectionFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function maskToken(): string
    {
        $token = (string) $this->access_token;

        if ($token === '') {
            return '****';
        }

        $last4 = substr($token, -4);

        return sprintf('****%s', $last4 === false ? '' : $last4);
    }

    public function needsSync(): bool
    {
        if ($this->last_sync_at === null) {
            return true;
        }

        return Carbon::parse($this->last_sync_at)->addHours(24)->isPast();
    }
}
