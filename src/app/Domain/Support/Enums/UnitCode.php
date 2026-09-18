<?php

declare(strict_types=1);

namespace App\Domain\Support\Enums;

/**
 * Language-neutral selling-unit codes (R14/§9). The display name is stored per
 * unit as `display_name_ar`/`display_name_en`; this code stays neutral for logic.
 */
enum UnitCode: string
{
    case Bag = 'bag';
    case Carton = 'carton';
    case Pack = 'pack';
    case Bottle = 'bottle';
    case Box = 'box';
    case Piece = 'piece';

    public function labelKey(): string
    {
        return 'domain.unit_code.'.$this->value;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
