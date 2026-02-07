<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorAuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\ResponseFactory;

Route::middleware(['web', 'resolve-tenant'])->group(function (): void {
    Route::get('/', function (ResponseFactory $inertia) {
        return $inertia->render('Home', [
            'appName' => config('app.name'),
        ]);
    })->name('home');

    Route::get('/setup/organization', function (ResponseFactory $inertia) {
        return $inertia->render('Auth/Register', [
            'status' => __('Set up your organization to continue.'),
        ]);
    })->name('setup.organization');

    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])
            ->middleware('throttle:login')
            ->name('login.attempt');

        Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [RegisterController::class, 'register'])
            ->middleware('throttle:login')
            ->name('register.store');

        Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])
            ->name('password.request');
        Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])
            ->middleware('throttle:login')
            ->name('password.email');
        Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetPassword'])
            ->name('password.reset');
        Route::post('/reset-password', [PasswordResetController::class, 'updatePassword'])
            ->middleware('throttle:login')
            ->name('password.update');
    });

    Route::middleware(['auth', 'verified', 'has-organization'])->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/2fa-setup', [TwoFactorAuthController::class, 'show2faForm'])->name('2fa.setup');
        Route::post('/2fa-setup', [TwoFactorAuthController::class, 'enable2fa'])
            ->middleware('throttle:two-factor')
            ->name('2fa.enable');
        Route::delete('/2fa-setup', [TwoFactorAuthController::class, 'disable2fa'])->name('2fa.disable');
        Route::post('/2fa-verify', [TwoFactorAuthController::class, 'verify2fa'])
            ->middleware('throttle:two-factor')
            ->name('2fa.verify');

        Route::get('/password-confirm', function (ResponseFactory $inertia) {
            return $inertia->render('Auth/PasswordConfirm');
        })->name('password.confirm');

        Route::get('/settings/profile', function (ResponseFactory $inertia) {
            return $inertia->render('Settings/Profile');
        })->name('settings.profile');

        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    });
});
