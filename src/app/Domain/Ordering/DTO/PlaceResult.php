<?php

declare(strict_types=1);

namespace App\Domain\Ordering\DTO;

use App\Models\Order;

/**
 * Outcome of an order-placement attempt (prompt 42 §9). `placed` carries the created order;
 * `review` means terms changed / blockers exist and the customer must re-review (no order created).
 */
final readonly class PlaceResult
{
    public const STATUS_PLACED = 'placed';

    public const STATUS_REVIEW = 'review';

    private function __construct(
        public string $status,
        public ?Order $order = null,
        public ?CheckoutReview $review = null,
    ) {
    }

    public static function placed(Order $order): self
    {
        return new self(self::STATUS_PLACED, order: $order);
    }

    public static function reviewRequired(CheckoutReview $review): self
    {
        return new self(self::STATUS_REVIEW, review: $review);
    }

    public function isPlaced(): bool
    {
        return $this->status === self::STATUS_PLACED;
    }
}
