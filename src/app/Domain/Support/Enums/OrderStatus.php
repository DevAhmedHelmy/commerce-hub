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

    /**
     * Allowed forward transitions (prompt 42 §10, R10) — cancellation follows the matrix below.
     *
     * @return list<self>
     */
    public function forwardTransitions(): array
    {
        return match ($this) {
            self::New => [self::Confirmed],
            self::Confirmed => [self::Preparing],
            self::Preparing => [self::OutForDelivery],
            self::OutForDelivery => [self::Delivered],
            self::Delivered, self::Cancelled => [],
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Delivered || $this === self::Cancelled;
    }

    /** Admin may cancel any non-terminal order (new/confirmed/preparing/out_for_delivery). */
    public function isAdminCancellable(): bool
    {
        return ! $this->isTerminal();
    }

    /** Customers may self-cancel only while the order is still new. */
    public function isCustomerCancellable(): bool
    {
        return $this === self::New;
    }
}
