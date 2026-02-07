<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(private readonly TenantManager $tenantManager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $organization = $this->tenantManager->resolveFromRequest($request);

        if ($organization !== null) {
            $this->tenantManager->setCurrentOrganization($organization);
            try {
                return $next($request);
            } finally {
                $this->tenantManager->clearOrganization();
            }
        }

        if ($this->isPublicRoute($request)) {
            return $next($request);
        }

        return $this->missingOrganizationResponse($request);
    }

    private function isPublicRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        return in_array($routeName, [
            'home',
            'login',
            'register',
            'password.request',
            'password.reset',
            'api.v1.health',
        ], true);
    }

    private function missingOrganizationResponse(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Organization context could not be resolved.'),
            ], 403);
        }

        return redirect()->route('setup.organization');
    }
}
