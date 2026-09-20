<?php

declare(strict_types=1);

namespace App\Domain\Support\Enums;

/**
 * Language-neutral delivery-discount type (R6/C2). Saving computation lives in the
 * Delivery domain (Phase G); this enum only names the neutral identifiers.
 */
enum DiscountType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';
    case FreeDelivery = 'free_delivery';

    public function labelKey(): string
    {
        return 'domain.discount_type.'.$this->value;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
