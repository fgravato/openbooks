<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        $validated = validator($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => ['required', 'string', 'confirmed', ...$this->passwordRules()],
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();
    }
}
