<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreignId('category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->unsignedBigInteger('recurring_expense_id')->nullable();
            $table->string('vendor');
            $table->text('description');
            $table->bigInteger('amount');
            $table->char('currency_code', 3)->default('USD');
            $table->string('tax_name')->nullable();
            $table->decimal('tax_percent', 5, 2)->nullable();
            $table->bigInteger('tax_amount')->default(0);
            $table->date('date');
            $table->string('receipt_path')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('is_billable')->default(false);
            $table->boolean('is_reimbursable')->default(false);
            $table->decimal('markup_percent', 5, 2)->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->unsignedBigInteger('bank_transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'date']);
            $table->index(['organization_id', 'category_id']);
            $table->index(['organization_id', 'client_id']);
            $table->index(['organization_id', 'project_id']);
            $table->index(['organization_id', 'invoice_id']);
            $table->index('recurring_expense_id');
            $table->index('bank_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
