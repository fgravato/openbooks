<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->char('currency_code', 3)->default('USD');
            $table->string('timezone')->default('UTC');
            $table->string('logo_path')->nullable();
            $table->json('settings')->nullable();
            $table->string('subscription_tier')->default('lite');
            $table->timestamps();

            $table->index('owner_id');
            $table->index('subscription_tier');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
