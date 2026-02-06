<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('bank_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->string('institution_name');
            $table->string('institution_id');
            $table->text('access_token');
            $table->string('item_id');
            $table->string('account_mask', 4);
            $table->string('account_type');
            $table->bigInteger('balance_current')->default(0);
            $table->bigInteger('balance_available')->default(0);
            $table->char('currency_code', 3)->default('USD');
            $table->timestamp('last_sync_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->index(['organization_id', 'account_type']);
            $table->unique(['organization_id', 'item_id', 'account_mask']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_connections');
    }
};
