<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * White-label client branding on the landing settings singleton (prompt 48 §7/§8): company name +
 * light/dark logos + favicon. Reused across public/customer/admin surfaces. Additive; nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->string('company_name_ar')->nullable()->after('site_title_en');
            $table->string('company_name_en')->nullable()->after('company_name_ar');
            $table->string('logo_light_path')->nullable()->after('hero_image_path');
            $table->string('logo_dark_path')->nullable()->after('logo_light_path');
            $table->string('favicon_path')->nullable()->after('logo_dark_path');
        });
    }

    public function down(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            $table->dropColumn(['company_name_ar', 'company_name_en', 'logo_light_path', 'logo_dark_path', 'favicon_path']);
        });
    }
};
