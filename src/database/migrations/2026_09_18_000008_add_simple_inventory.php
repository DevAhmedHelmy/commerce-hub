<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Simple inventory (prompt 32 / R24): per-unit stock balance on product_units plus an
 * append-only inventory_adjustments audit trail (data-model #6/#18). Stock is never
 * negative and is written only via InventoryService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->unsignedInteger('stock_quantity')->default(0)->after('base_price');
            $table->unsignedInteger('low_stock_threshold')->nullable()->after('stock_quantity');
        });

        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_unit_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // neutral: initial|manual_add|manual_remove|order|order_cancel_restore|correction
            $table->integer('quantity_delta'); // signed
            $table->unsignedInteger('quantity_before');
            $table->unsignedInteger('quantity_after');
            $table->string('reason')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable(); // immutable: no updated_at

            $table->index(['product_unit_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');

        Schema::table('product_units', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity', 'low_stock_threshold']);
        });
    }
};
