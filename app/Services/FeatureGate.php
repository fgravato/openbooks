<?php

declare(strict_types=1);

namespace App\Services;

use App\Domains\Identity\Models\Organization;
use Illuminate\Auth\Access\AuthorizationException;

readonly class FeatureGate
{
    public const array ALL_FEATURES = [
        'invoices',
        'clients',
        'expenses',
        'basic_reports',
        'projects',
        'time_tracking',
        'automation',
        'advanced_reports',
        'multi_currency',
        'approvals',
        'api_access',
        'sso',
        'audit_logs',
        'custom_roles',
        'priority_support',
    ];

    public function __construct(private TenantManager $tenantManager)
    {
    }

    public function isEnabled(string $feature, ?Organization $org = null): bool
    {
        $organization = $org ?? $this->tenantManager->getCurrentOrganization();

        if (! $organization instanceof Organization) {
            return false;
        }

        return $organization->isFeatureEnabled($feature);
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableFeatures(?Organization $org = null): array
    {
        $organization = $org ?? $this->tenantManager->getCurrentOrganization();

        if (! $organization instanceof Organization) {
            return [];
        }

        return array_values(array_filter(
            self::ALL_FEATURES,
            fn (string $feature): bool => $organization->isFeatureEnabled($feature),
        ));
    }

    /**
     * @throws AuthorizationException
     */
    public function requireFeature(string $feature): void
    {
        if ($this->isEnabled($feature)) {
            return;
        }

        throw new AuthorizationException(__('This feature is not available on your subscription tier.'));
    }
}
