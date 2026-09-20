<?php

declare(strict_types=1);

namespace App\Domain\Support\Enums;

/**
 * Language-neutral product availability (FR-014..FR-016). `inactive` is never shown
 * to customers; `out_of_stock` is viewable-not-orderable; `available` is orderable.
 */
enum AvailabilityStatus: string
{
    case Available = 'available';
    case OutOfStock = 'out_of_stock';
    case Inactive = 'inactive';

    /** Orderable = available only (out-of-stock is viewable but not orderable). */
    public function isOrderable(): bool
    {
        return $this === self::Available;
    }

    /** Inactive products are hidden from customers entirely. */
    public function isVisibleToCustomers(): bool
    {
        return $this !== self::Inactive;
    }

    public function labelKey(): string
    {
        return 'domain.availability.'.$this->value;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
