<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Payments\CreditNoteController;
use App\Http\Controllers\Api\Payments\PaymentController;
use App\Http\Controllers\Api\Payments\StripeWebhookController;
use App\Http\Resources\Identity\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api', 'resolve-tenant'])->group(function (): void {
    Route::get('/health', static function (): \Illuminate\Http\JsonResponse {
        return response()->json([
            'status' => 'ok',
            'service' => 'openbooks-api',
        ]);
    })->name('api.v1.health');

    Route::post('/payments/webhooks/stripe', [StripeWebhookController::class, 'handle'])
        ->name('api.v1.payments.webhooks.stripe');

    Route::middleware(['auth:sanctum,api', 'has-organization'])->group(function (): void {
        Route::get('/user', static function (Request $request): UserResource {
            return UserResource::make($request->user()?->loadMissing('organization'));
        })->name('api.v1.user.show');

        Route::get('/payments', [PaymentController::class, 'index'])->name('api.v1.payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('api.v1.payments.store');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('api.v1.payments.show');
        Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('api.v1.payments.refund');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('api.v1.payments.receipt');

        Route::get('/credit-notes', [CreditNoteController::class, 'index'])->name('api.v1.credit-notes.index');
        Route::post('/credit-notes', [CreditNoteController::class, 'store'])->name('api.v1.credit-notes.store');
        Route::get('/credit-notes/{creditNote}', [CreditNoteController::class, 'show'])->name('api.v1.credit-notes.show');
        Route::post('/credit-notes/{creditNote}/apply', [CreditNoteController::class, 'apply'])->name('api.v1.credit-notes.apply');
        Route::post('/credit-notes/{creditNote}/void', [CreditNoteController::class, 'void'])->name('api.v1.credit-notes.void');
    });
});
