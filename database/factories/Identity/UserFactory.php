<?php

declare(strict_types=1);

namespace Database\Factories\Identity;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Domains\Identity\Enums\Role;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => Role::Employee->value,
            'avatar' => null,
            'mfa_secret' => null,
            'email_verified_at' => \now(),
            'last_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }
}
