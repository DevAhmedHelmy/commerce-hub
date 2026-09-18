<?php

namespace App\Providers;

use App\Domain\Auth\Contracts\OtpProvider;
use App\Domain\Auth\Providers\LogOtpProvider;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the OTP delivery channel by driver (R7). Only the dev `log` driver
        // exists in MVP; a real SMS/WhatsApp provider is added later and selected here.
        $this->app->bind(OtpProvider::class, function (): OtpProvider {
            return match (config('otp.driver')) {
                'log' => new LogOtpProvider(),
                default => throw new InvalidArgumentException(
                    'Unsupported OTP driver: '.(string) config('otp.driver')
                ),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
