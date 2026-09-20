<?php

declare(strict_types=1);

namespace App\Domain\Support;

/**
 * Presentation-boundary formatter for {@see Money}. This is the ONLY place currency
 * rendering happens — Blade templates must never concatenate `ج` themselves.
 *
 * Output uses Latin digits with thousands grouping and the Arabic pound symbol:
 * `444 ج`, `1,250 ج`, `12,500 ج`. Piastres render only when a value carries a
 * non-zero minor part (e.g. a computed percentage discount → `12.50 ج`); stored
 * values are never pre-rounded for display (R2).
 */
final class MoneyFormatter
{
    public const SYMBOL = 'ج';

    /** Full display string including the currency symbol, e.g. `1,250 ج`. */
    public static function format(Money $money): string
    {
        return self::amount($money).' '.self::SYMBOL;
    }

    /** Numeric part only (no symbol), grouped with Latin digits. */
    public static function amount(Money $money): string
    {
        $major = intdiv($money->minorUnits, Money::MINOR_PER_MAJOR);
        $remainder = $money->minorUnits % Money::MINOR_PER_MAJOR;

        $grouped = number_format($major);

        if ($remainder === 0) {
            return $grouped;
        }

        return $grouped.'.'.str_pad((string) $remainder, 2, '0', STR_PAD_LEFT);
    }
}
