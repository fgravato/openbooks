<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->string('vendor');
            $table->text('description');
            $table->bigInteger('estimated_amount');
            $table->string('frequency');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_occurrence_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->index(['organization_id', 'next_occurrence_date']);
            $table->index(['organization_id', 'frequency']);
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->foreign('recurring_expense_id')
                ->references('id')
                ->on('recurring_expenses')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->dropForeign(['recurring_expense_id']);
        });

        Schema::dropIfExists('recurring_expenses');
    }
};
