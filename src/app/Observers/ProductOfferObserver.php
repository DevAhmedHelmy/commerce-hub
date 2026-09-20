<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Models\ProductOffer;

/**
 * Audits offer changes (prompt 39/41 §16): price/date/active create/update/delete.
 */
final class ProductOfferObserver
{
    private const FIELDS = ['offer_price', 'starts_at', 'ends_at', 'is_active', 'title_ar', 'title_en'];

    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function created(ProductOffer $offer): void
    {
        $this->audit->record(AuditAction::CREATED, $offer, [], $this->audit->snapshot($offer, self::FIELDS), [
            'product_unit_id' => $offer->product_unit_id,
        ]);
    }

    public function updated(ProductOffer $offer): void
    {
        [$old, $new] = $this->audit->changes($offer, self::FIELDS);

        if ($new === []) {
            return;
        }

        if (array_keys($new) === ['is_active']) {
            $action = $offer->is_active ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED;
        } elseif (array_key_exists('offer_price', $new)) {
            $action = AuditAction::PRICE_CHANGED;
        } else {
            $action = AuditAction::UPDATED;
        }

        $this->audit->record($action, $offer, $old, $new, ['product_unit_id' => $offer->product_unit_id]);
    }

    public function deleted(ProductOffer $offer): void
    {
        $this->audit->record(AuditAction::DELETED, $offer, $this->audit->snapshot($offer, self::FIELDS), [], [
            'product_unit_id' => $offer->product_unit_id,
        ]);
    }
}
