<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use RuntimeException;

/**
 * Thrown when a stock change would drive a unit's balance below zero (manual correction
 * or order deduction). Stock is never negative (FR-073/BR-013).
 */
final class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $productUnitId,
        public readonly int $available,
        public readonly int $requested,
    ) {
        parent::__construct(
            "Insufficient stock for product unit {$productUnitId}: available {$available}, requested {$requested}."
        );
    }
}
