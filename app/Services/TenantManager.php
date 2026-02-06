<?php

declare(strict_types=1);

namespace App\Services;

use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantManager
{
    private ?Organization $currentOrganization = null;

    public function setCurrentOrganization(Organization $org): void
    {
        $this->currentOrganization = $org;
    }

    public function getCurrentOrganization(): ?Organization
    {
        return $this->currentOrganization;
    }

    public function clearOrganization(): void
    {
        $this->currentOrganization = null;
    }

    public function resolveFromRequest(Request $request): ?Organization
    {
        $user = $request->user();

        if ($user instanceof User && is_int($user->organization_id)) {
            $organization = Organization::query()
                ->withoutGlobalScopes()
                ->find($user->organization_id);

            if ($organization instanceof Organization) {
                return $organization;
            }
        }

        $organization = $this->resolveFromBearerToken($request->bearerToken());

        if ($organization instanceof Organization) {
            return $organization;
        }

        $host = strtolower((string) $request->getHost());
        $subdomain = $this->extractSubdomain($host);

        if ($subdomain === null) {
            return null;
        }

        return $this->resolveFromSubdomain($subdomain);
    }

    public function resolveFromSubdomain(string $subdomain): ?Organization
    {
        return Organization::query()
            ->withoutGlobalScopes()
            ->where('slug', $subdomain)
            ->first();
    }

    public function resolveFromToken(string $token): ?Organization
    {
        $organizationId = DB::table('oauth_access_tokens')
            ->where('id', $token)
            ->where('revoked', false)
            ->value('organization_id');

        if (! is_int($organizationId)) {
            return null;
        }

        return Organization::query()
            ->withoutGlobalScopes()
            ->find($organizationId);
    }

    private function resolveFromBearerToken(?string $token): ?Organization
    {
        if (! is_string($token) || $token === '') {
            return null;
        }

        return $this->resolveFromToken($this->extractPassportTokenId($token));
    }

    private function extractPassportTokenId(string $token): string
    {
        return str_contains($token, '|')
            ? (string) explode('|', $token, 2)[0]
            : $token;
    }

    private function extractSubdomain(string $host): ?string
    {
        if ($host === '' || $host === 'localhost') {
            return null;
        }

        $segments = explode('.', $host);

        return match (true) {
            count($segments) >= 3 => in_array($segments[0], ['www', 'api'], true) ? null : $segments[0],
            count($segments) === 2 && $segments[1] === 'localhost' => $segments[0],
            default => null,
        };
    }
}
