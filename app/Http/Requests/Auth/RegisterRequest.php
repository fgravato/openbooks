<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Actions\Fortify\PasswordValidationRules;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'string', 'confirmed', ...$this->passwordRules()],
            'organization_name' => ['required', 'string', 'max:255'],
            'organization_slug' => ['required', 'string', 'max:255', Rule::unique(Organization::class, 'slug')],
            'terms' => ['accepted'],
        ];
    }
}
