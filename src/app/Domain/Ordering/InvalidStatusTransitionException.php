<?php

declare(strict_types=1);

namespace App\Domain\Ordering;

use App\Domain\Support\Enums\OrderStatus;
use RuntimeException;

/**
 * Thrown when an order status change is not permitted by the lifecycle/cancellation matrix (R10).
 */
final class InvalidStatusTransitionException extends RuntimeException
{
    public function __construct(public readonly OrderStatus $from, public readonly OrderStatus $to)
    {
        parent::__construct("Invalid order status transition: {$from->value} → {$to->value}.");
    }
}
