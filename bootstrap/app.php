<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\RequireOrganization;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\ValidateSignature;
use App\Providers\AuthServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Cookie\Middleware\EncryptCookies as FrameworkEncryptCookies;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: TrustProxies::class);
        // $middleware->preventRequestsDuringMaintenance(PreventRequestsDuringMaintenance::class);

        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'resolve-tenant' => ResolveTenant::class,
            'has-organization' => RequireOrganization::class,
            'subscription' => CheckSubscription::class,
            'signed' => ValidateSignature::class,
            'encrypt.cookies' => FrameworkEncryptCookies::class,
        ]);
    })
    ->withProviders([
        AuthServiceProvider::class,
        FortifyServiceProvider::class,
        RouteServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReportDuplicates();
    })
    ->create();
