<?php

declare(strict_types=1);

use App\Domain\Support\Money;
use App\Domain\Support\MoneyFormatter;

it('constructs from minor and major units', function () {
    expect(Money::fromMinor(44400)->minorUnits)->toBe(44400)
        ->and(Money::fromMajor(444)->minorUnits)->toBe(44400)
        ->and(Money::zero()->minorUnits)->toBe(0)
        ->and(Money::fromMinor(100)->currency)->toBe('EGP');
});

it('rejects negative amounts', function () {
    Money::fromMinor(-1);
})->throws(InvalidArgumentException::class);

it('adds and subtracts deterministically', function () {
    expect(Money::fromMajor(200)->plus(Money::fromMajor(50))->minorUnits)->toBe(25000)
        ->and(Money::fromMajor(200)->minus(Money::fromMajor(160))->minorUnits)->toBe(4000);
});

it('never subtracts below zero', function () {
    Money::fromMajor(150)->minus(Money::fromMajor(160));
})->throws(InvalidArgumentException::class);

it('multiplies by an integer quantity for line totals', function () {
    expect(Money::fromMajor(160)->times(3)->minorUnits)->toBe(48000)
        ->and(Money::fromMajor(200)->times(0)->isZero())->toBeTrue();
});

it('computes percentages with half-up rounding defined once', function () {
    // 3.33 EGP × 15% = 0.4995 → rounds half-up to 0.50 (50 minor units).
    expect(Money::fromMinor(333)->percentage(15)->minorUnits)->toBe(50)
        // exact, no rounding
        ->and(Money::fromMinor(31000)->percentage(50)->minorUnits)->toBe(15500)
        // exact half → up
        ->and(Money::fromMinor(1)->percentage(50)->minorUnits)->toBe(1)
        // just below half → down
        ->and(Money::fromMinor(99)->percentage(50)->minorUnits)->toBe(50);
});

it('rejects out-of-range percentages', function () {
    Money::fromMajor(100)->percentage(101);
})->throws(InvalidArgumentException::class);

it('compares amounts', function () {
    expect(Money::fromMajor(160)->compareTo(Money::fromMajor(200)))->toBe(-1)
        ->and(Money::fromMajor(200)->compareTo(Money::fromMajor(200)))->toBe(0)
        ->and(Money::fromMajor(200)->compareTo(Money::fromMajor(160)))->toBe(1)
        ->and(Money::fromMajor(160)->lessThan(Money::fromMajor(200)))->toBeTrue()
        ->and(Money::fromMajor(200)->greaterThanOrEqualTo(Money::fromMajor(200)))->toBeTrue()
        ->and(Money::fromMajor(200)->equals(Money::fromMajor(200)))->toBeTrue();
});

it('selects the lowest amount (lower-of rule foundation)', function () {
    // Normal 200 / Tier 170 / Offer 160 → 160
    expect(Money::fromMajor(200)->min(Money::fromMajor(170), Money::fromMajor(160))->minorUnits)->toBe(16000)
        // Normal 200 / Tier 150 / Offer 160 → 150
        ->and(Money::fromMajor(200)->min(Money::fromMajor(150), Money::fromMajor(160))->minorUnits)->toBe(15000);
});

it('caps at a ceiling (delivery-saving foundation)', function () {
    expect(Money::fromMajor(80)->cappedAt(Money::fromMajor(50))->minorUnits)->toBe(5000)
        ->and(Money::fromMajor(30)->cappedAt(Money::fromMajor(50))->minorUnits)->toBe(3000);
});

it('rejects arithmetic across currencies', function () {
    Money::fromMinor(100, 'EGP')->plus(Money::fromMinor(100, 'USD'));
})->throws(InvalidArgumentException::class);

it('formats whole EGP with Latin grouping and the ج symbol', function () {
    expect(MoneyFormatter::format(Money::fromMajor(444)))->toBe('444 ج')
        ->and(MoneyFormatter::format(Money::fromMajor(1250)))->toBe('1,250 ج')
        ->and(MoneyFormatter::format(Money::fromMajor(12500)))->toBe('12,500 ج');
});

it('formats piastres with two decimals only when present', function () {
    expect(MoneyFormatter::format(Money::fromMinor(1250)))->toBe('12.50 ج')
        ->and(MoneyFormatter::format(Money::fromMinor(1205)))->toBe('12.05 ج')
        ->and(MoneyFormatter::amount(Money::fromMajor(1250)))->toBe('1,250');
});
