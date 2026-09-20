<?php

declare(strict_types=1);

namespace App\Domain\Delivery;

use App\Domain\Support\Money;

/**
 * Result of a delivery-fee calculation (prompt 42 §7). The final fee is the base area fee minus the
 * single largest eligible discount saving (never stacked, floored at zero).
 */
final readonly class DeliveryQuote
{
    public function __construct(
        public Money $baseFee,
        public Money $discountAmount,
        public Money $finalFee,
        public ?int $appliedRuleId = null,
        public ?string $appliedRuleType = null,
    ) {
    }
}
