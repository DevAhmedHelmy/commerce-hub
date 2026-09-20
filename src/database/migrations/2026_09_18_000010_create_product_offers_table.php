<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * product_offers — time-boxed discounted unit price (data-model #8, R3/BR-005). Applied
 * only while active AND now within [starts_at, ends_at]. offer_price is minor units.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_unit_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('offer_price'); // minor units
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->timestamps();

            $table->index(['product_unit_id', 'is_active', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_offers');
    }
};
