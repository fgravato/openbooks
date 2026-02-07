<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('bank_connection_id')->constrained('bank_connections')->cascadeOnDelete();
            $table->string('transaction_id');
            $table->bigInteger('amount');
            $table->char('currency_code', 3)->default('USD');
            $table->date('date');
            $table->string('name');
            $table->string('merchant_name')->nullable();
            $table->json('category')->nullable();
            $table->boolean('pending')->default(false);
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'date']);
            $table->index(['organization_id', 'pending']);
            $table->index(['organization_id', 'expense_id']);
            $table->unique(['organization_id', 'bank_connection_id', 'transaction_id']);
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->foreign('bank_transaction_id')
                ->references('id')
                ->on('bank_transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->dropForeign(['bank_transaction_id']);
        });

        Schema::dropIfExists('bank_transactions');
    }
};
