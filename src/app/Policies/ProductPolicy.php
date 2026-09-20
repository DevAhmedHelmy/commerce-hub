<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Product/catalog authorization (prompt 40). Maps Filament resource abilities to granular
 * permissions; super_admin is short-circuited by the Gate::before bypass in AppServiceProvider.
 */
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('products.view');
    }

    public function create(User $user): bool
    {
        return $user->can('products.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('products.update');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('products.update');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('products.update');
    }
}
