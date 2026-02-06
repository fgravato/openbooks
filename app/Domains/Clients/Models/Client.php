<?php

declare(strict_types=1);

namespace App\Domains\Clients\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use BelongsToOrganization;
    use HasFactory;
    use SoftDeletes;

    protected string $table = 'clients';

    protected array $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone',
        'address',
        'currency_code',
        'language',
        'payment_terms',
        'late_fee_type',
        'late_fee_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'late_fee_amount' => 'integer',
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany('App\\Domains\\Invoicing\\Models\\Invoice');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany('App\\Domains\\Expenses\\Models\\Expense');
    }

    public function projects(): HasMany
    {
        return $this->hasMany('App\\Domains\\Projects\\Models\\Project');
    }
}
