<?php

declare(strict_types=1);

namespace App\Domain\Ordering;

/**
 * Human-friendly order number derived from the auto-increment id plus a fixed offset (R9),
 * e.g. id 1 → ORD-100001. Allocated inside the creation transaction; `UNIQUE(order_number)` is
 * the concurrency backstop.
 */
final class OrderNumber
{
    public const OFFSET = 100000;

    public static function fromId(int $id): string
    {
        return 'ORD-'.str_pad((string) ($id + self::OFFSET), 6, '0', STR_PAD_LEFT);
    }
}
