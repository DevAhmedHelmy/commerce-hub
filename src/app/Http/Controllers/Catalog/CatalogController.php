<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalog;

use App\Domain\Catalog\CatalogService;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Customer catalog browsing (US2, FR-011..FR-017). Thin orchestration over
 * {@see CatalogService}; visibility rules live in the service/model scopes.
 */
class CatalogController extends Controller
{
    public function __construct(private readonly CatalogService $catalog)
    {
    }

    public function home(): View
    {
        return view('catalog.home', [
            'categories' => $this->catalog->activeCategories(),
            // Featured = active-offer products (same MVP rule as the landing), read live.
            'featured' => app(\App\Domain\Landing\LandingPageService::class)->featuredProducts(8),
        ]);
    }

    public function categories(): View
    {
        return view('catalog.categories', [
            'categories' => $this->catalog->activeCategories(),
        ]);
    }

    public function category(Category $category): View
    {
        abort_unless($category->is_active, 404);

        return view('catalog.category', [
            'category' => $category,
            'products' => $this->catalog->productsInCategory($category),
        ]);
    }

    public function search(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        return view('catalog.search', [
            'query' => $query,
            'results' => $query === '' ? null : $this->catalog->search($query),
        ]);
    }

    public function product(Product $product): View
    {
        abort_unless($this->catalog->isViewable($product), 404);

        return view('catalog.product', [
            'product' => $this->catalog->productDetail($product),
        ]);
    }
}
