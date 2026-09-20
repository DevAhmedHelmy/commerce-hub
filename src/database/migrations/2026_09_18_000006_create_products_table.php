<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * products — sellable items (data-model #5, R3/R14). Pricing lives on units, not here.
 * Bilingual `name`/`description`; single natural `brand` (no `*_ar`/`*_en`). Availability
 * is a neutral enum (available|out_of_stock|inactive).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('brand')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('image_path')->nullable();
            $table->string('availability')->default('available'); // neutral: available|out_of_stock|inactive
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category_id', 'availability', 'sort_order']);
            $table->index('name_ar');
            $table->index('brand');
            $table->index('availability');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
