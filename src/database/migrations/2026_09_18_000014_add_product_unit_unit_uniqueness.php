<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce the documented data-integrity constraint that a product may not reference the same
 * generic unit for both its primary and sub level (data-model §6: primary `unit_id` ≠ sub
 * `unit_id`). A `UNIQUE(product_id, unit_id)` index makes this impossible at the DB layer,
 * complementing the existing `UNIQUE(product_id, level)`. Additive; safe on empty/valid data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->unique(['product_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'unit_id']);
        });
    }
};
