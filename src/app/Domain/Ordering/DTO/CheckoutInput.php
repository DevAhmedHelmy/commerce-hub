<?php

declare(strict_types=1);

namespace App\Domain\Ordering\DTO;

/**
 * Customer-chosen checkout inputs (delivery step). `submissionToken` provides duplicate-submit
 * idempotency for placement (prompt 42 §9).
 */
final readonly class CheckoutInput
{
    public function __construct(
        public int $addressId,
        public string $deliveryDate,
        public int $slotId,
        public string $paymentMethod = 'cod',
        public ?string $submissionToken = null,
    ) {
    }
}
