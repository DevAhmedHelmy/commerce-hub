<?php

declare(strict_types=1);

namespace App\Domain\Support\Enums;

/**
 * Language-neutral payment method. MVP is cash-on-delivery only (no online payment).
 */
enum PaymentMethod: string
{
    case CashOnDelivery = 'cod';

    public function labelKey(): string
    {
        return 'domain.payment_method.'.$this->value;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
