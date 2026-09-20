<?php

declare(strict_types=1);

namespace App\Domain\Cart;

use App\Domain\Support\Money;

/**
 * Recomputed cart snapshot for display (prompt 42 §6). The qualifying subtotal is the effective
 * product subtotal (after tiers/offers) of AVAILABLE lines only — it never includes any delivery
 * fee or discount (those are Phase G/H concerns).
 */
final readonly class CartView
{
    /** @param list<CartLine> $lines */
    public function __construct(
        public array $lines,
        public Money $productSubtotal,
        public bool $meetsMinimum,
        public Money $minimumOrderAmount,
        public Money $remainingToMinimum,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }
}
