<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\FeatureGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckSubscription
{
    public function __construct(private readonly FeatureGate $featureGate)
    {
    }

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if ($this->featureGate->isEnabled($feature)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('This feature is not available in your current plan.'),
                'feature' => $feature,
            ], 403);
        }

        throw new HttpException(403, __('This feature is not available in your current plan.'));
    }
}
