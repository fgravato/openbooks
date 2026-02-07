<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_method_configs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('gateway')->default('stripe');
            $table->string('gateway_payment_method_id');
            $table->string('type');
            $table->string('last_four', 4)->nullable();
            $table->string('brand')->nullable();
            $table->unsignedTinyInteger('exp_month')->nullable();
            $table->unsignedSmallInteger('exp_year')->nullable();
            $table->boolean('is_default')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['organization_id', 'client_id']);
            $table->unique(['organization_id', 'gateway_payment_method_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_method_configs');
    }
};
