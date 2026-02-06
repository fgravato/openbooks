<?php

declare(strict_types=1);

namespace Database\Seeders\Identity;

use App\Domains\Identity\Enums\Role;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        Organization::query()->each(function (Organization $organization): void {
            $owner = User::query()->firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'email' => "owner@{$organization->slug}.openbooks.test",
                ],
                [
                    'name' => $organization->name.' Owner',
                    'password' => Hash::make('password'),
                    'role' => Role::Owner->value,
                    'avatar' => null,
                    'mfa_secret' => null,
                    'email_verified_at' => \now(),
                    'last_login_at' => null,
                ],
            );

            if ($organization->owner_id !== $owner->id) {
                $organization->owner_id = $owner->id;
                $organization->save();
            }

            User::factory()->count(2)->create([
                'organization_id' => $organization->id,
                'role' => Role::Employee->value,
            ]);
        });
    }
}
