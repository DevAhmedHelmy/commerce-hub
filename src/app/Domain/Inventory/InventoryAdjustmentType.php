<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

/**
 * Language-neutral inventory adjustment reasons (prompt 32 §4). Presentation maps these
 * to Arabic labels; logic branches on the identifier.
 */
enum InventoryAdjustmentType: string
{
    case Initial = 'initial';
    case ManualAdd = 'manual_add';
    case ManualRemove = 'manual_remove';
    case Order = 'order';
    case OrderCancelRestore = 'order_cancel_restore';
    case Correction = 'correction';

    public function labelKey(): string
    {
        return 'inventory.type.'.$this->value;
    }

    /** Manual admin actions (vs automatic order/restore). */
    public function isManual(): bool
    {
        return in_array($this, [self::Initial, self::ManualAdd, self::ManualRemove, self::Correction], true);
    }
}
