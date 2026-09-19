<?php

declare(strict_types=1);

namespace App\Domain\Pricing;

use App\Domain\Support\Money;

/**
 * Immutable result of pricing one sellable product unit at a quantity (data-model #15 fields,
 * prompt 41 §10). All amounts are integer-minor-unit {@see Money}; no presentation formatting here.
 * `appliedSource` explains which eligible price won under the lower-of rule (never stacked, R3).
 */
final readonly class PriceResult
{
    public const SOURCE_NORMAL = 'normal';

    public const SOURCE_TIER = 'tier';

    public const SOURCE_OFFER = 'offer';

    public function __construct(
        public Money $base,
        public ?Money $tier,
        public ?Money $offer,
        public Money $applied,
        public string $appliedSource,
        public int $quantity,
        public Money $lineTotal,
        public Money $unitSaving,
        public ?int $tierId = null,
        public ?int $offerId = null,
    ) {
    }

    public function hasDiscount(): bool
    {
        return $this->appliedSource !== self::SOURCE_NORMAL;
    }
}
