<?php

declare(strict_types=1);

namespace App\Domain\Ordering\DTO;

use App\Domain\Cart\CartLine;
use App\Domain\Delivery\DeliveryQuote;
use App\Domain\Support\Money;

/**
 * Server-computed checkout summary (prompt 42 §8). `changes` (diff vs last-seen terms) and
 * `blockers` (below-minimum, inactive area/slot, out-of-stock) both prevent placement until the
 * customer re-reviews. Message values are translation keys.
 */
final readonly class CheckoutReview
{
    /**
     * @param  list<CartLine>  $lines
     * @param  list<string>  $changes
     * @param  list<string>  $blockers
     */
    public function __construct(
        public array $lines,
        public Money $productSubtotal,
        public ?DeliveryQuote $deliveryQuote,
        public Money $finalTotal,
        public bool $meetsMinimum,
        public Money $minimumOrderAmount,
        public array $changes,
        public array $blockers,
        public ?string $deliveryAreaName = null,
    ) {
    }

    public function canPlace(): bool
    {
        return $this->lines !== []
            && $this->changes === []
            && $this->blockers === []
            && $this->meetsMinimum;
    }
}
