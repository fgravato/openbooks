<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class RequireOrganization
{
    public function __construct(private readonly TenantManager $tenantManager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningInConsole()) {
            return $next($request);
        }

        if ($this->tenantManager->getCurrentOrganization() !== null) {
            return $next($request);
        }

        throw new HttpException(403, __('Organization not found for request context.'));
    }
}
