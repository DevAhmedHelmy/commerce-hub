<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * product_units — selling units (data-model #6, R3/R14). `code` is a neutral identifier;
 * `display_name`/`package_description` are bilingual. `base_price` is the normal unit
 * price in integer minor units (never float). `conversion_factor` reserved for future
 * inventory (unused in MVP).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('code'); // neutral: bag|carton|pack|bottle|...
            $table->string('display_name_ar');
            $table->string('display_name_en')->nullable();
            $table->string('package_description_ar')->nullable();
            $table->string('package_description_en')->nullable();
            $table->unsignedBigInteger('base_price'); // minor units (EGP piastres)
            $table->unsignedInteger('conversion_factor')->nullable(); // reserved, unused in MVP
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'code']);
            $table->index(['product_id', 'is_active', 'sort_order']);
            $table->index(['product_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
