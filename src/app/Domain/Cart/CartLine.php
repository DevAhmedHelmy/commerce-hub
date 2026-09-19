<?php

declare(strict_types=1);

namespace App\Domain\Cart;

use App\Domain\Pricing\PriceResult;
use App\Models\CartItem;
use App\Models\ProductUnit;

/**
 * One recomputed cart line (prompt 42 §6): the item, its unit, the server-computed effective price
 * for the current quantity, and current availability. Unavailable lines are flagged and excluded
 * from the qualifying subtotal.
 */
final readonly class CartLine
{
    public function __construct(
        public CartItem $item,
        public ProductUnit $unit,
        public PriceResult $price,
        public bool $available,
        public ?string $unavailableReasonKey = null,
    ) {
    }
}
