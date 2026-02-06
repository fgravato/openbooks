<?php

declare(strict_types=1);

namespace App\Domains\Identity\Models;

use App\Domains\Identity\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use Laravel\Sanctum\HasApiTokens as HasSanctumApiTokens;
use App\Traits\BelongsToOrganization;

class User extends Authenticatable implements MustVerifyEmail
{
    use BelongsToOrganization;
    use HasApiTokens;
    use HasFactory;
    use HasSanctumApiTokens;
    use Notifiable;

    protected string $table = 'users';

    protected array $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'mfa_secret',
        'email_verified_at',
        'last_login_at',
    ];

    protected array $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    protected function casts(): array
    {
        return [
            'role' => Role::class,
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany('App\\Domains\\TimeTracking\\Models\\TimeEntry');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany('App\\Domains\\Expenses\\Models\\Expense');
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->role->permissions();

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public function isOwner(): bool
    {
        return $this->role === Role::Owner;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [Role::Owner, Role::Admin], true);
    }
}
