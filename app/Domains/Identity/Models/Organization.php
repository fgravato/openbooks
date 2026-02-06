<?php

declare(strict_types=1);

namespace App\Domains\Identity\Models;

use App\Domains\Identity\Enums\SubscriptionTier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected string $table = 'organizations';

    protected array $fillable = [
        'name',
        'slug',
        'owner_id',
        'currency_code',
        'timezone',
        'logo_path',
        'settings',
        'subscription_tier',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'subscription_tier' => SubscriptionTier::class,
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany('App\\Domains\\Clients\\Models\\Client');
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

    public function isFeatureEnabled(string $feature): bool
    {
        $featureOverrides = $this->settings['features'] ?? [];

        if (array_key_exists($feature, $featureOverrides)) {
            return (bool) $featureOverrides[$feature];
        }

        return in_array($feature, $this->subscription_tier->features(), true);
    }
}
