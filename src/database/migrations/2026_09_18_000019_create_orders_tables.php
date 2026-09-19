<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * orders + order_items (data-model #14/#15, R9, Principle V). Immutable commercial snapshots:
 * catalog/pricing changes never alter a placed order. Money is integer minor units. `order_number`
 * is a human-friendly unique code; `submission_token` gives duplicate-submit idempotency. Each item
 * snapshots the selected unit, its conversion factor, and the normalized sub-unit quantity deducted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('submission_token')->nullable()->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();

            // Customer + delivery snapshots (never mutated post-placement).
            $table->string('business_name')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp_phone')->nullable();
            $table->string('delivery_area_name')->nullable();
            $table->string('address_line')->nullable();
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->string('unit')->nullable();
            $table->string('landmark')->nullable();
            $table->string('delivery_notes')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('delivery_slot_label')->nullable();
            $table->string('slot_start_time')->nullable();
            $table->string('slot_end_time')->nullable();

            // Money (minor units), server-authoritative at placement.
            $table->unsignedBigInteger('product_subtotal');
            $table->unsignedBigInteger('base_delivery_fee');
            $table->unsignedBigInteger('delivery_discount')->default(0);
            $table->unsignedBigInteger('final_delivery_fee');
            $table->unsignedBigInteger('final_total');

            $table->string('payment_method')->default('cod');
            $table->string('status')->default('new');
            $table->string('cancellation_reason')->nullable();
            $table->string('cancelled_by')->nullable(); // customer|admin
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('delivery_date');
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_unit_id')->nullable()->constrained()->nullOnDelete();

            // Snapshots.
            $table->string('product_name');
            $table->string('brand')->nullable();
            $table->string('unit_name');
            $table->string('unit_level')->nullable();
            $table->string('package_description')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('conversion_factor')->default(1);
            $table->unsignedInteger('sub_unit_quantity'); // normalized deducted quantity

            // Money (minor units).
            $table->unsignedBigInteger('base_unit_price');
            $table->unsignedBigInteger('applied_unit_price');
            $table->string('applied_source'); // normal|tier|offer
            $table->unsignedBigInteger('unit_saving')->default(0);
            $table->unsignedBigInteger('line_total');
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
