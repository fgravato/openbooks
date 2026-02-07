<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Clients\ClientController;
use App\Http\Controllers\Api\Clients\ContactController;
use App\Http\Controllers\Api\Expenses\BankConnectionController;
use App\Http\Controllers\Api\Expenses\ExpenseCategoryController;
use App\Http\Controllers\Api\Expenses\ExpenseController;
use App\Http\Controllers\Api\Expenses\ExpenseImportController;
use App\Http\Controllers\Api\Invoices\InvoiceController;
use App\Http\Controllers\Api\Invoices\InvoiceLineController;
use App\Http\Controllers\Api\Invoices\InvoiceProfileController;
use App\Http\Controllers\Api\Payments\CreditNoteController;
use App\Http\Controllers\Api\Payments\PaymentController;
use App\Http\Controllers\Api\Payments\StripeWebhookController;
use App\Http\Resources\Identity\OrganizationResource;
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
        // Identity
        Route::get('/user', static function (Request $request): UserResource {
            return UserResource::make($request->user()?->loadMissing('organization'));
        })->name('api.v1.user.show');

        Route::get('/organization', static function (Request $request): OrganizationResource {
            return OrganizationResource::make($request->user()?->organization);
        })->name('api.v1.organization.show');

        // Invoices
        Route::apiResource('invoices', InvoiceController::class);
        Route::post('invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate']);
        Route::post('invoices/{invoice}/send', [InvoiceController::class, 'markAsSent']);
        Route::post('invoices/{invoice}/email', [InvoiceController::class, 'sendEmail']);
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf.download');
        Route::get('invoices/{invoice}/share-link', [InvoiceController::class, 'getShareableLink']);

        // Invoice Lines
        Route::post('invoices/{invoice}/lines', [InvoiceLineController::class, 'store']);
        Route::put('lines/{line}', [InvoiceLineController::class, 'update']);
        Route::delete('lines/{line}', [InvoiceLineController::class, 'destroy']);
        Route::post('invoices/{invoice}/lines/reorder', [InvoiceLineController::class, 'reorder']);

        // Invoice Profiles
        Route::apiResource('invoice-profiles', InvoiceProfileController::class);
        Route::post('invoice-profiles/{profile}/pause', [InvoiceProfileController::class, 'pause']);
        Route::post('invoice-profiles/{profile}/resume', [InvoiceProfileController::class, 'resume']);
        Route::post('invoice-profiles/{profile}/generate', [InvoiceProfileController::class, 'generateNow']);

        // Clients
        Route::apiResource('clients', ClientController::class);
        Route::get('clients/{client}/statement', [ClientController::class, 'getStatement']);

        // Contacts
        Route::get('clients/{client}/contacts', [ContactController::class, 'index']);
        Route::post('clients/{client}/contacts', [ContactController::class, 'store']);
        Route::get('contacts/{contact}', [ContactController::class, 'show']);
        Route::put('contacts/{contact}', [ContactController::class, 'update']);
        Route::delete('contacts/{contact}', [ContactController::class, 'destroy']);
        Route::post('contacts/{contact}/primary', [ContactController::class, 'setPrimary']);

        // Expenses
        Route::apiResource('expenses', ExpenseController::class);
        Route::post('expenses/{expense}/submit', [ExpenseController::class, 'submitForApproval']);
        Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approve']);
        Route::post('expenses/{expense}/reject', [ExpenseController::class, 'reject']);
        Route::post('expenses/{expense}/receipt', [ExpenseController::class, 'attachReceipt']);
        Route::delete('expenses/{expense}/receipt', [ExpenseController::class, 'removeReceipt']);
        Route::get('expenses/{expense}/receipt', [ExpenseController::class, 'downloadReceipt']);

        // Expense Categories
        Route::apiResource('expense-categories', ExpenseCategoryController::class);

        // Bank Connections
        Route::apiResource('bank-connections', BankConnectionController::class);
        Route::post('bank-connections/{bankConnection}/sync', [BankConnectionController::class, 'sync']);

        // Expense Import
        Route::post('expenses/import/preview', [ExpenseImportController::class, 'previewCsv']);
        Route::post('expenses/import', [ExpenseImportController::class, 'importCsv']);

        // Payments (Existing)
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
