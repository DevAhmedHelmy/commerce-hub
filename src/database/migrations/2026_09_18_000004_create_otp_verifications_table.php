<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * otp_verifications — hashed one-time phone challenges (data-model #16, R7, Principle VI).
 * The code is stored ONLY as a hash, never plaintext, and never logged in production.
 * Expiry, one-time consume, resend cooldown, and attempt limits are enforced in
 * OtpService + Laravel's rate limiter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('resend_count')->default(0);
            $table->timestamp('last_sent_at')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['phone', 'expires_at']);
            $table->index('consumed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
