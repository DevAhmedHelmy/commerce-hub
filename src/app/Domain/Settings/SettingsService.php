<?php

declare(strict_types=1);

namespace App\Domain\Settings;

use App\Domain\Support\LocalizedContent;
use App\Domain\Support\Money;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Reads/writes business configuration (data-model #17, R16). Settings are stable,
 * high-read data, so the full key/value map is cached and invalidated on write.
 * Money settings are stored as integer minor units and returned as {@see Money}.
 */
final class SettingsService
{
    private const CACHE_KEY = 'settings.all';

    /** Effective product-subtotal minimum (C7/BR-002); 0 = no minimum until seeded. */
    public function minimumOrderAmount(): Money
    {
        return Money::fromMinor((int) $this->get('minimum_order_amount', '0'));
    }

    /** Business/contact info for the landing page and checkout, locale-resolved. */
    public function businessInfo(): array
    {
        return [
            'name' => LocalizedContent::resolve(
                $this->get('business_name_ar'),
                $this->get('business_name_en'),
            ),
            'address' => LocalizedContent::resolve(
                $this->get('business_address_ar'),
                $this->get('business_address_en'),
            ),
            'phone' => $this->get('business_phone'),
            'whatsapp' => $this->get('business_whatsapp'),
        ];
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->all()[$key] ?? $default;
    }

    /** Persist one or many settings, then invalidate the cache. */
    public function update(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value === null ? null : (string) $value],
            );
        }

        $this->flush();
    }

    public function set(string $key, ?string $value): void
    {
        $this->update([$key => $value]);
    }

    /** @return array<string, string|null> */
    private function all(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            static fn (): array => Setting::query()->pluck('value', 'key')->all(),
        );
    }

    private function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
