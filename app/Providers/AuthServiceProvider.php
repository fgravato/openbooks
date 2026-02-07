<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Expenses\Models\Expense;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Policies\ExpenseCategoryPolicy;
use App\Domains\Expenses\Policies\ExpensePolicy;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceProfile;
use App\Domains\Invoicing\Policies\InvoicePolicy;
use App\Domains\Invoicing\Policies\InvoiceProfilePolicy;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Policies\PaymentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Invoice::class => InvoicePolicy::class,
        InvoiceProfile::class => InvoiceProfilePolicy::class,
        Payment::class => PaymentPolicy::class,
        Expense::class => ExpensePolicy::class,
        ExpenseCategory::class => ExpenseCategoryPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
