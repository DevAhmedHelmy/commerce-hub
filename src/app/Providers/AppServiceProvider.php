<?php

namespace App\Providers;

use App\Domain\Auth\Contracts\OtpProvider;
use App\Domain\Auth\Providers\LogOtpProvider;
use App\Models\DeliveryArea;
use App\Models\DeliveryDiscountRule;
use App\Models\DeliverySlot;
use App\Models\LandingPageSetting;
use App\Models\LandingSection;
use App\Models\LandingSectionItem;
use App\Models\Product;
use App\Models\ProductOffer;
use App\Models\ProductPriceTier;
use App\Models\ProductUnit;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use App\Observers\DeliveryAreaObserver;
use App\Observers\DeliveryDiscountRuleObserver;
use App\Observers\DeliverySlotObserver;
use App\Observers\LandingObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductOfferObserver;
use App\Observers\ProductPriceTierObserver;
use App\Observers\ProductUnitObserver;
use App\Observers\SettingObserver;
use App\Observers\UnitObserver;
use App\Observers\UserObserver;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;

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
        User::observe(UserObserver::class);
        ProductPriceTier::observe(ProductPriceTierObserver::class);
        ProductOffer::observe(ProductOfferObserver::class);
        DeliveryArea::observe(DeliveryAreaObserver::class);
        DeliverySlot::observe(DeliverySlotObserver::class);
        DeliveryDiscountRule::observe(DeliveryDiscountRuleObserver::class);
        LandingPageSetting::observe(LandingObserver::class);
        LandingSection::observe(LandingObserver::class);
        LandingSectionItem::observe(LandingObserver::class);

        // RBAC (prompt 40): super_admin bypasses every ability; the package Role model lives
        // outside policy auto-discovery, so its policy is registered explicitly.
        Gate::before(fn (User $user, string $ability): ?bool => $user->hasRole('super_admin') ? true : null);
        Gate::policy(Role::class, RolePolicy::class);
    }
}
