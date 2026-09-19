<?php

namespace App\Providers;

use App\Domain\Auth\Contracts\OtpProvider;
use App\Domain\Auth\Providers\LogOtpProvider;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Setting;
use App\Models\Unit;
use App\Observers\ProductObserver;
use App\Observers\ProductUnitObserver;
use App\Observers\SettingObserver;
use App\Observers\UnitObserver;
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
        // Central audit wiring (prompt 39 §9): critical entities delegate create/update/delete
        // to their observers, which call the single AdminAuditService.
        Product::observe(ProductObserver::class);
        ProductUnit::observe(ProductUnitObserver::class);
        Unit::observe(UnitObserver::class);
        Setting::observe(SettingObserver::class);
    }
}
