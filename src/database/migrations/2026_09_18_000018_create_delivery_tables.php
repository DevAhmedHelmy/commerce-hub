<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * delivery_areas / delivery_slots / delivery_discount_rules (data-model #11/#12/#13, R5/R6). Slots
 * are recurring weekday templates (ISO 1=Mon..7=Sun, no capacity). Discount rules are evaluated by
 * DeliveryService (largest single saving, no stacking, floor 0). All money is integer minor units.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar')->unique();
            $table->string('name_en')->nullable();
            $table->unsignedBigInteger('base_fee'); // minor units
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('delivery_slots', function (Blueprint $table) {
            $table->id();
            $table->string('label_ar');
            $table->string('label_en')->nullable();
            $table->unsignedTinyInteger('day_of_week'); // ISO 1..7
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['day_of_week', 'is_active', 'sort_order']);
        });

        Schema::create('delivery_discount_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type'); // fixed|percentage|free_delivery
            $table->unsignedBigInteger('value')->default(0); // minor units (fixed) / percent (percentage)
            $table->unsignedBigInteger('min_subtotal')->default(0); // qualifying effective product subtotal
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'min_subtotal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_discount_rules');
        Schema::dropIfExists('delivery_slots');
        Schema::dropIfExists('delivery_areas');
    }
};
