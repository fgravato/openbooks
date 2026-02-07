<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Clients\Models\Client;
use App\Domains\Clients\Models\Contact;
use App\Domains\Expenses\Models\BankConnection;
use App\Domains\Expenses\Models\Expense;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLine;
use App\Domains\Invoicing\Models\InvoiceProfile;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('api', static function (Request $request): Limit {
            return Limit::perMinute(120)->by((string) $request->user()?->id ?: $request->ip());
        });

        $this->configureRouteModelBindings();
    }

    protected function configureRouteModelBindings(): void
    {
        Route::model('invoice', Invoice::class);
        Route::model('client', Client::class);
        Route::model('expense', Expense::class);
        Route::model('profile', InvoiceProfile::class);
        Route::model('line', InvoiceLine::class);
        Route::model('contact', Contact::class);
        Route::model('category', ExpenseCategory::class);
        Route::model('connection', BankConnection::class);
    }
}
