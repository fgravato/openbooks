<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('frequency');
            $table->unsignedSmallInteger('custom_days')->nullable();
            $table->date('next_issue_date');
            $table->date('end_date')->nullable();
            $table->unsignedInteger('occurrences_remaining')->nullable();
            $table->boolean('auto_send')->default(false);
            $table->json('template_data');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'is_active', 'next_issue_date']);
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreign('invoice_profile_id')->references('id')->on('invoice_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropForeign(['invoice_profile_id']);
        });

        Schema::dropIfExists('invoice_profiles');
    }
};
