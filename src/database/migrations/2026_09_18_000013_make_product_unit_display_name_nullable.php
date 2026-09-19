<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * In the two-level model (prompt 37/38) a product_unit's display name is an OPTIONAL
 * per-product override — the generic unit's name is the fallback. Make `display_name_ar`
 * nullable (it was required under the old selling-unit model).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->string('display_name_ar')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->string('display_name_ar')->nullable(false)->change();
        });
    }
};
