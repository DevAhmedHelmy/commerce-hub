<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Catalog read model (FR-011..FR-017, R15). Customer-facing queries exclude inactive
 * products/categories and use indexed MySQL `LIKE` search over Arabic name + brand.
 * Pricing is evaluated separately (Phase E); listings use a lightweight baseline.
 */
final class CatalogService
{
    /** @return Collection<int, Category> */
    public function activeCategories(): Collection
    {
        return Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->get();
    }

    /** Available + out-of-stock products in a category; inactive excluded (FR-016). */
    public function productsInCategory(Category $category, int $perPage = 20): LengthAwarePaginator
    {
        return $category->products()
            ->visible()
            ->with(['units' => fn ($q) => $q->active()->orderBy('base_price')])
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->paginate($perPage);
    }

    /** Search visible products by Arabic/English name or brand (FR-012, R15). */
    public function search(string $query, int $perPage = 20): LengthAwarePaginator
    {
        $term = trim($query);
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return Product::query()
            ->visible()
            ->with(['units' => fn ($q) => $q->active()->orderBy('base_price')])
            ->where(function ($q) use ($like): void {
                $q->where('name_ar', 'like', $like)
                    ->orWhere('name_en', 'like', $like)
                    ->orWhere('brand', 'like', $like);
            })
            ->orderBy('name_ar')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Product detail with active units + category (units for selection; pricing in Phase E). */
    public function productDetail(Product $product): Product
    {
        return $product->load([
            'category',
            'units' => fn ($q) => $q->active()->orderBy('sort_order')->orderBy('id'),
        ]);
    }

    public function isViewable(Product $product): bool
    {
        return $product->availability->isVisibleToCustomers();
    }
}
