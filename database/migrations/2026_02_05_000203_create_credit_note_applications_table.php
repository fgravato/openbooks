<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('credit_note_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('credit_note_id')->constrained('credit_notes')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->timestamp('applied_at');

            $table->index(['credit_note_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_applications');
    }
};
