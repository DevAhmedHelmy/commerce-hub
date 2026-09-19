<?php

declare(strict_types=1);

use App\Domain\Inventory\ProductUnitConverter;
use App\Models\ProductUnit;

beforeEach(function () {
    $this->converter = new ProductUnitConverter();
    $this->carton = new ProductUnit(['conversion_to_sub_unit' => 12]); // 1 carton = 12 pieces
    $this->piece = new ProductUnit(['conversion_to_sub_unit' => 1]);
});

it('converts primary and sub quantities to sub-units', function () {
    expect($this->converter->toSubUnits($this->carton, 1))->toBe(12)
        ->and($this->converter->toSubUnits($this->carton, 10))->toBe(120)
        ->and($this->converter->toSubUnits($this->piece, 7))->toBe(7);
});

it('sums a mixed order to normalized sub-units (2 cartons + 5 pieces = 29)', function () {
    $total = $this->converter->toSubUnits($this->carton, 2) + $this->converter->toSubUnits($this->piece, 5);

    expect($total)->toBe(29);
});

it('formats a sub-unit balance as primary + remainder (125 => 10 carton + 5 piece)', function () {
    expect($this->converter->formatStock(125, 12))->toBe(['primary' => 10, 'sub' => 5]);
});
