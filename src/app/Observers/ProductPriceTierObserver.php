<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Models\ProductPriceTier;

/**
 * Audits quantity-tier changes (prompt 39/41 §16): threshold and price create/update/delete.
 */
final class ProductPriceTierObserver
{
    private const FIELDS = ['min_quantity', 'unit_price', 'is_active'];

    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function created(ProductPriceTier $tier): void
    {
        $this->audit->record(AuditAction::CREATED, $tier, [], $this->audit->snapshot($tier, self::FIELDS), [
            'product_unit_id' => $tier->product_unit_id,
        ]);
    }

    public function updated(ProductPriceTier $tier): void
    {
        [$old, $new] = $this->audit->changes($tier, self::FIELDS);

        if ($new === []) {
            return;
        }

        $action = array_key_exists('unit_price', $new) ? AuditAction::PRICE_CHANGED : AuditAction::UPDATED;

        $this->audit->record($action, $tier, $old, $new, ['product_unit_id' => $tier->product_unit_id]);
    }

    public function deleted(ProductPriceTier $tier): void
    {
        $this->audit->record(AuditAction::DELETED, $tier, $this->audit->snapshot($tier, self::FIELDS), [], [
            'product_unit_id' => $tier->product_unit_id,
        ]);
    }
}
