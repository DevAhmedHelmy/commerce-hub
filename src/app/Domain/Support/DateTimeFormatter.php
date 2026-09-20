<?php

declare(strict_types=1);

namespace App\Domain\Support;

use Carbon\Carbon;
use DateTimeInterface;

/**
 * Centralized locale-aware date/time presentation (§37/§38, R13). Stored timestamps
 * stay language-neutral (UTC); this converts to a displayed string at the boundary
 * only. Digits stay Latin (D2); month/day names follow the active app locale so a
 * future English locale needs no code change — only a locale switch.
 *
 * Business date/time values (delivery date, slot times) remain deterministic; this
 * formatter never participates in business rules.
 */
final class DateTimeFormatter
{
    /** e.g. `18 سبتمبر 2026` in Arabic, `18 September 2026` in English. */
    public static function date(DateTimeInterface|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return self::carbon($value)->translatedFormat('j F Y');
    }

    /** 24-hour clock, Latin digits, e.g. `13:00`. Neutral time, not localized names. */
    public static function time(DateTimeInterface|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return self::carbon($value)->format('H:i');
    }

    /** Combined date + time, e.g. `18 سبتمبر 2026 · 13:00`. */
    public static function dateTime(DateTimeInterface|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return self::date($value).' · '.self::time($value);
    }

    /** A time window from two `HH:MM[:SS]` values, e.g. `13:00 – 16:00`. */
    public static function timeRange(
        DateTimeInterface|string|null $start,
        DateTimeInterface|string|null $end,
    ): string {
        return trim(self::time($start).' – '.self::time($end), ' –');
    }

    private static function carbon(DateTimeInterface|string $value): Carbon
    {
        return Carbon::parse($value)->locale(app()->getLocale());
    }
}
