<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * product_price_tiers — per-unit quantity breaks (data-model #7, R3). Optional; a
 * flat-priced unit has none. Tier applies for qty >= min_quantity up to the next tier.
 * unit_price is integer minor units.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_unit_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('min_quantity');
            $table->unsignedBigInteger('unit_price'); // minor units
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_unit_id', 'min_quantity']);
            $table->index(['product_unit_id', 'min_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_tiers');
    }
};
