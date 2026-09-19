<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Catalog\CatalogService;
use App\Domain\Settings\SettingsService;
use App\Models\Product;
use App\Models\ProductOffer;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Public Arabic landing page (US10). Reuses real catalog/offers/settings; renders gracefully when
 * empty. Featured = products with an active in-range offer only. CTA routes to sign-in.
 */
class LandingController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalog,
        private readonly SettingsService $settings,
    ) {
    }

    public function index(): View
    {
        $now = Carbon::now();

        $offeredProductIds = ProductOffer::query()
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->with('productUnit:id,product_id')
            ->get()
            ->pluck('productUnit.product_id')
            ->filter()
            ->unique()
            ->take(8);

        $featured = Product::query()
            ->visible()
            ->whereIn('id', $offeredProductIds)
            ->with(['units'])
            ->get();

        return view('landing.index', [
            'categories' => $this->catalog->activeCategories(),
            'featured' => $featured,
            'business' => $this->settings->businessInfo(),
        ]);
    }
}
