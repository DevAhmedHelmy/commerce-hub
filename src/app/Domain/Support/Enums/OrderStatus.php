<?php

declare(strict_types=1);

namespace App\Domain\Support\Enums;

/**
 * Language-neutral order lifecycle status (R10). Arabic/English are presentation
 * labels resolved from the neutral value — never stored or branched on as text.
 * Transition rules live in the Ordering domain (Phase J), not on this enum.
 */
enum OrderStatus: string
{
    case New = 'new';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    /** Translation key for presentation; the enum itself never translates. */
    public function labelKey(): string
    {
        return 'domain.order_status.'.$this->value;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
