<?php

declare(strict_types=1);

namespace App\Domain\Landing;

use App\Models\Category;
use App\Models\LandingPageSetting;
use App\Models\LandingSection;
use App\Models\Product;
use App\Models\ProductOffer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Read model for the public landing page (prompt 46 §19/§20). Owns loading of admin-controlled
 * settings + active ordered sections (cached; invalidated on admin edits) and the dynamic
 * Categories / Featured Products which come from REAL domain models — never duplicated into landing
 * tables. Keeps Blade free of database logic. Dynamic pricing/offers are read live (never cached).
 */
final class LandingPageService
{
    private const CACHE_SETTINGS = 'landing.settings';

    private const CACHE_SECTIONS = 'landing.sections';

    public function settings(): LandingPageSetting
    {
        return Cache::rememberForever(self::CACHE_SETTINGS, static fn () => LandingPageSetting::singleton());
    }

    /** Active sections in configured order (with their marketing items). @return Collection<int,LandingSection> */
    public function sections(): Collection
    {
        return Cache::rememberForever(self::CACHE_SECTIONS, static fn () => LandingSection::query()
            ->where('is_active', true)
            ->with(['items' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get());
    }

    public function section(string $key): ?LandingSection
    {
        return $this->sections()->firstWhere('key', $key);
    }

    /** Active categories for the categories section, limited by its `settings.limit`. */
    public function categories(int $default = 8): Collection
    {
        $limit = $this->section('categories')?->limit($default) ?? $default;

        return Category::query()->where('is_active', true)->orderBy('sort_order')->limit($limit)->get();
    }

    /**
     * Featured products = active products with an active, in-range offer (MVP rule). Read live so
     * pricing/offer changes are never stale. Limited by the featured section's `settings.limit`.
     */
    public function featuredProducts(int $default = 8): Collection
    {
        $limit = $this->section('featured_products')?->limit($default) ?? $default;
        $now = Carbon::now();

        $productIds = ProductOffer::query()
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->with('productUnit:id,product_id')
            ->get()
            ->pluck('productUnit.product_id')
            ->filter()
            ->unique()
            ->take($limit);

        return Product::query()->visible()->whereIn('id', $productIds)->with('units')->get();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_SETTINGS);
        Cache::forget(self::CACHE_SECTIONS);
    }
}
