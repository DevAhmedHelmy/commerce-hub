<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Refactor product_units from independent "selling units" to a two-level product↔unit
 * configuration (prompt 37/38, data-model §6). Additive + data-preserving; no destructive
 * reset. Legacy rows are backfilled: each distinct legacy `code` becomes a `units` record,
 * rows default to `sub`/conversion 1 (identity — no invented factors), and a legacy default
 * unit becomes `primary`. A product carrying multiple legacy units that cannot resolve to a
 * single primary+sub pair is reported (unique(product_id, level) guard) rather than guessed.
 *
 * Inventory: the authoritative sub-unit balance stays on the (now sub-level) product_units
 * row's existing `stock_quantity`; inventory_adjustments gains the admin's input unit/qty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->restrictOnDelete();
            $table->string('level')->nullable()->after('unit_id'); // primary|sub
            $table->unsignedInteger('conversion_to_sub_unit')->default(1)->after('level');
            $table->boolean('is_sellable')->default(true)->after('conversion_to_sub_unit');
        });

        // Backfill legacy rows into the units module (no-op on an empty catalog).
        foreach (DB::table('product_units')->get() as $row) {
            $code = $row->code ?? 'piece';
            $unitId = DB::table('units')->where('code', $code)->value('id');
            if ($unitId === null) {
                $unitId = DB::table('units')->insertGetId([
                    'code' => $code,
                    'name_ar' => $row->display_name_ar ?? $code,
                    'name_en' => $row->display_name_en ?? null,
                    'is_active' => true,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('product_units')->where('id', $row->id)->update([
                'unit_id' => $unitId,
                'level' => ($row->is_default ?? false) ? 'primary' : 'sub',
                'conversion_to_sub_unit' => 1, // identity — real factors are set intentionally by admin
                'is_sellable' => true,
            ]);
        }

        Schema::table('product_units', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'code']);
            $table->dropIndex(['product_id', 'is_default']);
        });

        Schema::table('product_units', function (Blueprint $table) {
            $table->dropColumn(['code', 'is_default']);
        });

        Schema::table('product_units', function (Blueprint $table) {
            $table->unique(['product_id', 'level']);
            $table->index(['product_id', 'level']);
        });

        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->foreignId('input_unit_id')->nullable()->after('type')->constrained('units')->nullOnDelete();
            $table->integer('input_quantity')->nullable()->after('input_unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('input_unit_id');
            $table->dropColumn('input_quantity');
        });

        Schema::table('product_units', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'level']);
            $table->dropIndex(['product_id', 'level']);
            $table->string('code')->nullable();
            $table->boolean('is_default')->default(false);
        });

        Schema::table('product_units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
            $table->dropColumn(['level', 'conversion_to_sub_unit', 'is_sellable']);
        });
    }
};
