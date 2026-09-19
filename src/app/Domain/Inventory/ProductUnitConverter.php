<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Models\ProductUnit;

/**
 * Two-level unit conversion (prompt 37/38 §17). Pure quantity conversion — never touches
 * pricing. Primary quantity × conversion_to_sub_unit → sub-units; sub quantity is identity.
 * MVP is exactly two levels (primary → sub); no n-level trees.
 */
final class ProductUnitConverter
{
    /** Convert a quantity expressed in the given unit into authoritative sub-units. */
    public function toSubUnits(ProductUnit $unit, int $quantity): int
    {
        return $quantity * max(1, (int) $unit->conversion_to_sub_unit);
    }

    /**
     * Present an authoritative sub-unit balance as whole primary units + remainder sub-units.
     *
     * @return array{primary: int, sub: int}
     */
    public function formatStock(int $subUnitBalance, int $conversionToSubUnit): array
    {
        $factor = max(1, $conversionToSubUnit);

        return [
            'primary' => intdiv($subUnitBalance, $factor),
            'sub' => $subUnitBalance % $factor,
        ];
    }
}
