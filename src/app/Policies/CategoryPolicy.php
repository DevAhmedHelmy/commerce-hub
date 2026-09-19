<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * Categories are catalog structure (prompt 40 §11) — gated by the product permissions.
 */
class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('products.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->can('products.view');
    }

    public function create(User $user): bool
    {
        return $user->can('products.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('products.update');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can('products.update');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('products.update');
    }
}
