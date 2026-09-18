<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | OTP delivery driver
    |--------------------------------------------------------------------------
    | 'log' surfaces the code to a dev-only channel in non-production. A real
    | SMS/WhatsApp provider is bound in production (R7). Production MUST NOT use
    | the log driver.
    */
    'driver' => env('OTP_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Challenge parameters (config-driven per spec Assumptions / FR-004/FR-005)
    |--------------------------------------------------------------------------
    */
    'length' => (int) env('OTP_LENGTH', 6),
    'ttl_seconds' => (int) env('OTP_TTL_SECONDS', 300),          // 5-minute validity
    'resend_cooldown_seconds' => (int) env('OTP_RESEND_COOLDOWN', 60),
    'max_verify_attempts' => (int) env('OTP_MAX_VERIFY_ATTEMPTS', 5),

    // Per-phone request throttling window.
    'request' => [
        'max' => (int) env('OTP_REQUEST_MAX', 5),
        'window_seconds' => (int) env('OTP_REQUEST_WINDOW', 600),
    ],

];
