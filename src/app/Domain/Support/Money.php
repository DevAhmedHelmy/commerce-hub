<?php

declare(strict_types=1);

namespace App\Domain\Support;

use InvalidArgumentException;

/**
 * Immutable money value object — the single, authoritative money representation (R2).
 *
 * Every amount is stored and computed as an integer number of minor units (EGP
 * piastres; 1 EGP = 100). Binary floating point is NEVER used for money. Rounding
 * is defined once here (half-up to the minor unit) and reused by every percentage
 * calculation. Amounts are non-negative — this domain never carries negative money
 * (savings, fees, totals are all ≥ 0; the delivery floor is enforced by capping,
 * not by negative arithmetic).
 *
 * Formatting for display lives in {@see MoneyFormatter}, never here — the domain
 * boundary stays free of presentation concerns.
 */
final readonly class Money
{
    public const CURRENCY = 'EGP';

    public const MINOR_PER_MAJOR = 100;

    public function __construct(
        public int $minorUnits,
        public string $currency = self::CURRENCY,
    ) {
        if ($minorUnits < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }
    }

    public static function fromMinor(int $minorUnits, string $currency = self::CURRENCY): self
    {
        return new self($minorUnits, $currency);
    }

    /** Build from whole major units (whole EGP) — MVP pricing is entered in whole EGP. */
    public static function fromMajor(int $majorUnits, string $currency = self::CURRENCY): self
    {
        return new self($majorUnits * self::MINOR_PER_MAJOR, $currency);
    }

    public static function zero(string $currency = self::CURRENCY): self
    {
        return new self(0, $currency);
    }

    public function plus(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->minorUnits + $other->minorUnits, $this->currency);
    }

    public function minus(self $other): self
    {
        $this->assertSameCurrency($other);

        $result = $this->minorUnits - $other->minorUnits;

        if ($result < 0) {
            throw new InvalidArgumentException('Money subtraction cannot produce a negative amount.');
        }

        return new self($result, $this->currency);
    }

    /** Multiply by an integer quantity (e.g. line total = unit price × quantity). */
    public function times(int $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Money multiplier cannot be negative.');
        }

        return new self($this->minorUnits * $factor, $this->currency);
    }

    /**
     * Deterministic integer percentage (0–100) using the single half-up rule.
     * Used by delivery percentage discounts (Phase G) — never binary-float math.
     */
    public function percentage(int $percent): self
    {
        if ($percent < 0 || $percent > 100) {
            throw new InvalidArgumentException('Percentage must be between 0 and 100.');
        }

        return new self(self::roundHalfUp($this->minorUnits * $percent, 100), $this->currency);
    }

    /** -1, 0, 1 like the spaceship operator, currency-checked. */
    public function compareTo(self $other): int
    {
        $this->assertSameCurrency($other);

        return $this->minorUnits <=> $other->minorUnits;
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency && $this->minorUnits === $other->minorUnits;
    }

    public function lessThan(self $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    public function greaterThan(self $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    public function greaterThanOrEqualTo(self $other): bool
    {
        return $this->compareTo($other) >= 0;
    }

    public function isZero(): bool
    {
        return $this->minorUnits === 0;
    }

    /** Smallest of this and the given amounts — powers the lower-of pricing rule (R3). */
    public function min(self ...$others): self
    {
        $min = $this;

        foreach ($others as $other) {
            if ($other->lessThan($min)) {
                $min = $other;
            }
        }

        return $min;
    }

    /** Cap at a ceiling (e.g. a discount saving cannot exceed the base fee). */
    public function cappedAt(self $ceiling): self
    {
        return $this->greaterThan($ceiling) ? $ceiling : $this;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Currency mismatch: {$this->currency} vs {$other->currency}."
            );
        }
    }

    /** Round a non-negative rational to the nearest integer, ties going up. */
    private static function roundHalfUp(int $numerator, int $denominator): int
    {
        return intdiv($numerator * 2 + $denominator, $denominator * 2);
    }
}
