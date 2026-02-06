<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Domains\Identity\Enums\Role;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        $validated = validator($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'string', 'confirmed', ...$this->passwordRules()],
            'organization_name' => ['required', 'string', 'max:255'],
            'organization_slug' => ['nullable', 'string', 'max:255', Rule::unique(Organization::class, 'slug')],
        ])->validate();

        return DB::transaction(function () use ($validated): User {
            $organization = Organization::query()->create([
                'name' => $validated['organization_name'],
                'slug' => $this->resolveOrganizationSlug($validated),
                'owner_id' => null,
            ]);

            $user = User::query()->create([
                'organization_id' => $organization->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => Role::Owner->value,
            ]);

            $organization->update(['owner_id' => $user->id]);

            event(new Registered($user));

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveOrganizationSlug(array $validated): string
    {
        $slug = (string) ($validated['organization_slug'] ?? '');

        if ($slug !== '') {
            return Str::slug($slug);
        }

        $candidate = Str::slug((string) $validated['organization_name']);

        if ($candidate === '') {
            throw ValidationException::withMessages([
                'organization_slug' => __('A valid organization slug could not be generated.'),
            ]);
        }

        if (! Organization::query()->where('slug', $candidate)->exists()) {
            return $candidate;
        }

        return $candidate.'-'.Str::lower(Str::random(6));
    }
}
