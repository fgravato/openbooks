<?php

declare(strict_types=1);

namespace App\Http\Resources\Identity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Domains\Identity\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role?->value,
            'role_label' => $this->role?->label(),
            'permissions' => $this->role?->permissions() ?? [],
            'avatar_url' => $this->avatar ? Storage::url($this->avatar) : null,
            'has_2fa_enabled' => is_string($this->mfa_secret) && $this->mfa_secret !== '',
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'organization' => OrganizationResource::make($this->whenLoaded('organization')),
        ];
    }
}
