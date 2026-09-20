<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use RuntimeException;

/**
 * Thrown when an admin tries to change a product's primary→sub conversion factor while the
 * product still holds non-zero stock (prompt 37 §24 / prompt 38 §11). Existing normalized
 * sub-unit stock must never be silently reinterpreted under a new factor — the admin must
 * first reset/correct the balance to zero.
 */
final class ConversionChangeBlockedException extends RuntimeException
{
    public function __construct(
        public readonly int $productId,
        public readonly int $currentSubStock,
    ) {
        parent::__construct(
            "Cannot change conversion factor for product {$productId} while sub-unit stock is {$currentSubStock}; reconcile stock to zero first."
        );
    }
}
