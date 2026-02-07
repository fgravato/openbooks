<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('type')->default('item');
            $table->text('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->string('tax_name')->nullable();
            $table->decimal('tax_percent', 5, 2)->nullable();
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->foreignId('time_entry_id')->nullable()->constrained('time_entries')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('invoice_id');
            $table->index(['invoice_id', 'sort_order']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
