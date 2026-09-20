<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * categories — admin-managed product groupings (data-model #4, R14). Bilingual
 * `name`/`description` (`*_ar` required, `*_en` nullable); `is_active` hides from
 * customers. Neutral `slug` optional for stable routing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('name_ar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
