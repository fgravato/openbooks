<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Domains\Identity\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        $validated = validator($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user->getKey()),
            ],
            'avatar' => ['nullable', 'image', 'max:5120'],
        ])->validate();

        if (($validated['avatar'] ?? null) instanceof UploadedFile) {
            $this->updateAvatar($user, $validated['avatar']);
        }

        if (
            $validated['email'] !== $user->email
            && $user instanceof MustVerifyEmail
        ) {
            $this->updateVerifiedUser($user, $validated);

            return;
        }

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function updateVerifiedUser(User $user, array $validated): void
    {
        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }

    private function updateAvatar(User $user, UploadedFile $avatar): void
    {
        if (is_string($user->avatar) && $user->avatar !== '') {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $avatar->store('avatars', 'public');

        $user->forceFill([
            'avatar' => $path,
        ])->save();
    }
}
