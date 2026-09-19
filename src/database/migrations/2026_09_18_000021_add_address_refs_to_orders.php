<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Traceability references on orders (prompt 43/44): the chosen `customer_address_id` (nullable FK,
 * null-on-delete — the snapshot remains the historical truth) and a `delivery_area_id` snapshot
 * alongside the existing `delivery_area_name` snapshot, so a later area rename/deletion never
 * alters a placed order. Additive; existing orders keep null references.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_address_id')->nullable()->after('customer_id')->constrained('customer_addresses')->nullOnDelete();
            $table->unsignedBigInteger('delivery_area_id')->nullable()->after('delivery_area_name'); // snapshot ref only
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_address_id');
            $table->dropColumn('delivery_area_id');
        });
    }
};
