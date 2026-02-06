<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoice_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->text('header_html')->nullable();
            $table->text('footer_html')->nullable();
            $table->text('css_styles')->nullable();
            $table->string('logo_position')->default('left');
            $table->string('color_primary', 20)->default('#0f172a');
            $table->string('color_secondary', 20)->default('#334155');
            $table->string('paper_size', 16)->default('A4');
            $table->timestamps();

            $table->index(['organization_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_templates');
    }
};
