<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Resources\Identity\OrganizationResource;
use App\Http\Resources\Identity\UserResource;
use App\Services\FeatureGate;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        private readonly TenantManager $tenantManager,
        private readonly FeatureGate $featureGate,
    ) {}

    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $organization = $this->tenantManager->getCurrentOrganization();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() === null ? null : UserResource::make($request->user()->loadMissing('organization')),
            ],
            'tenant' => $organization === null ? null : OrganizationResource::make($organization),
            'features' => $this->featureGate->getAvailableFeatures($organization),
            'flash' => [
                'success' => static fn (): ?string => $request->session()->get('success'),
                'error' => static fn (): ?string => $request->session()->get('error'),
            ],
            'csrf_token' => csrf_token(),
            'ziggy' => static fn (): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
