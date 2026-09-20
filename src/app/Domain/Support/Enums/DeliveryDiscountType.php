<?php

declare(strict_types=1);

namespace App\Domain\Support\Enums;

/**
 * Language-neutral delivery-discount types (data-model #13, R6). `fixed` = flat money off;
 * `percentage` = integer percent of the base fee; `free_delivery` = full base fee waived.
 */
enum DeliveryDiscountType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';
    case FreeDelivery = 'free_delivery';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
