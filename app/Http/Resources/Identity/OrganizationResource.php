<?php

declare(strict_types=1);

namespace App\Http\Resources\Identity;

use App\Services\FeatureGate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'logo_url' => $this->logo_path ? Storage::url($this->logo_path) : null,
            'subscription_tier' => $this->subscription_tier?->value,
            'subscription_label' => $this->subscription_tier?->label(),
            'features' => array_values(array_filter(
                defined('App\Services\FeatureGate::ALL_FEATURES') ? FeatureGate::ALL_FEATURES : [],
                fn (string $feature): bool => $this->isFeatureEnabled($feature),
            )),
            'stats' => [
                'invoices_count' => $this->invoices_count ?? 0,
                'clients_count' => $this->clients_count ?? 0,
                'expenses_count' => $this->expenses_count ?? 0,
                'projects_count' => $this->projects_count ?? 0,
            ],
        ];
    }
}
