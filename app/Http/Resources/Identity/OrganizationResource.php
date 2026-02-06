<?php

declare(strict_types=1);

namespace App\Http\Resources\Identity;

use App\Services\FeatureGate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domains\Identity\Models\Organization
 */
class OrganizationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'currency_code' => $this->currency_code,
            'timezone' => $this->timezone,
            'logo_path' => $this->logo_path,
            'subscription_tier' => $this->subscription_tier?->value,
            'available_features' => array_values(array_filter(
                FeatureGate::ALL_FEATURES,
                fn (string $feature): bool => $this->isFeatureEnabled($feature),
            )),
        ];
    }
}
