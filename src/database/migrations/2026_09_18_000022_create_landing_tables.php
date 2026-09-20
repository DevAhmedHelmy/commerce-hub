<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backend-controlled landing page (prompt 46). A controlled configuration feature — NOT a generic
 * CMS/page-builder. `landing_page_settings` is a singleton (hero/contact/socials/CTA label);
 * `landing_sections` are the ordered, toggleable page sections (dynamic Categories/Featured Products
 * pull from real domain models — never duplicated here); `landing_section_items` are repeatable
 * marketing cards (benefits/steps) only. Money/products/offers are never stored here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_title_ar')->nullable();
            $table->string('site_title_en')->nullable();
            $table->string('hero_title_ar')->nullable();
            $table->string('hero_title_en')->nullable();
            $table->string('hero_subtitle_ar')->nullable();
            $table->string('hero_subtitle_en')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('primary_cta_label_ar')->nullable();
            $table->string('primary_cta_label_en')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('landing_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // neutral: hero|categories|featured_products|features|about|contact
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->string('subtitle_ar')->nullable();
            $table->string('subtitle_en')->nullable();
            $table->text('content_ar')->nullable();
            $table->text('content_en')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('settings')->nullable(); // validated: e.g. {"limit": 6}
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('landing_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_section_id')->constrained()->cascadeOnDelete();
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->string('description_ar')->nullable();
            $table->string('description_en')->nullable();
            $table->string('image_path')->nullable();
            $table->string('icon')->nullable();
            $table->string('link_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['landing_section_id', 'is_active', 'sort_order'], 'landing_items_section_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_section_items');
        Schema::dropIfExists('landing_sections');
        Schema::dropIfExists('landing_page_settings');
    }
};
