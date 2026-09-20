<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * customer_addresses — delivery details (data-model #3, C6). MVP exposes ONE default
 * per customer; schema is many-capable for the future. `delivery_area_id` references
 * delivery_areas (created in Phase G) — kept nullable + indexed here without a hard FK
 * so US1 (auth/onboarding) is independently buildable before delivery config exists;
 * area validity is enforced in the Delivery domain once areas are configured.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('delivery_area_id')->nullable();
            $table->boolean('is_default')->default(true);
            $table->string('address_line');
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->string('unit')->nullable();                // apartment / shop / unit
            $table->string('landmark')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('delivery_area_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
