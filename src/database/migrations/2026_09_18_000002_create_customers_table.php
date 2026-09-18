<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * customers — B2B ordering customers authenticated by phone OTP (data-model #2, R8).
 * Separate identity from admin `users`. Soft-deletes preserve order-history links.
 * onboarding_completed_at gates ordering (FR-009). No KYC/tax fields (C5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();                 // E.164-normalized login
            $table->string('business_name')->nullable();       // primary commercial identity
            $table->string('contact_person_name')->nullable();
            $table->string('whatsapp_phone')->nullable();
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('business_name');                    // admin directory search (FR-056)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
