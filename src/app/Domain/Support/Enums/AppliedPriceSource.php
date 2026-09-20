<?php

declare(strict_types=1);

namespace App\Domain\Support\Enums;

/**
 * Which price candidate won the deterministic lower-of evaluation (R3). Used by
 * PriceResult (Phase E) to explain "best · tier" / "best · offer" in the UI.
 */
enum AppliedPriceSource: string
{
    case Normal = 'normal';
    case Tier = 'tier';
    case Offer = 'offer';

    public function labelKey(): string
    {
        return 'domain.applied_price_source.'.$this->value;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
