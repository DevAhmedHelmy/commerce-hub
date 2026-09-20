<?php

declare(strict_types=1);

namespace App\Domain\Audit;

/**
 * Language-neutral audit action codes (prompt 39 §4). Stored verbatim; Arabic labels are
 * presentation-only and resolved via {@see self::label()}.
 */
final class AuditAction
{
    public const CREATED = 'created';

    public const UPDATED = 'updated';

    public const DELETED = 'deleted';

    public const ACTIVATED = 'activated';

    public const DEACTIVATED = 'deactivated';

    public const PRICE_CHANGED = 'price_changed';

    public const STOCK_ADDED = 'stock_added';

    public const STOCK_REMOVED = 'stock_removed';

    public const STOCK_CORRECTED = 'stock_corrected';

    // Reserved for later phases (orders/delivery) — kept here so wiring stays consistent.
    public const ORDER_STATUS_CHANGED = 'order_status_changed';

    public const ORDER_CANCELLED = 'order_cancelled';

    /** @return array<string,string> */
    public static function labels(): array
    {
        return [
            self::CREATED => 'إنشاء',
            self::UPDATED => 'تعديل',
            self::DELETED => 'حذف',
            self::ACTIVATED => 'تفعيل',
            self::DEACTIVATED => 'إلغاء تفعيل',
            self::PRICE_CHANGED => 'تغيير السعر',
            self::STOCK_ADDED => 'إضافة مخزون',
            self::STOCK_REMOVED => 'خصم مخزون',
            self::STOCK_CORRECTED => 'تصحيح المخزون',
            self::ORDER_STATUS_CHANGED => 'تغيير حالة الطلب',
            self::ORDER_CANCELLED => 'إلغاء الطلب',
        ];
    }

    public static function label(string $action): string
    {
        return self::labels()[$action] ?? $action;
    }
}
