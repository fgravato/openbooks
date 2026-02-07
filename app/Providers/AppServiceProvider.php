<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Payments\Gateways\PaymentGatewayInterface;
use App\Domains\Payments\Gateways\StripeGateway;
use App\Services\FeatureGate;
use App\Services\TenantManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantManager::class);
        $this->app->singleton(FeatureGate::class);
        $this->app->bind(PaymentGatewayInterface::class, StripeGateway::class);
    }

    public function boot(): void {}
}
