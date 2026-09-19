<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Models\Product;

/**
 * Audits product create/update/delete (prompt 39 §1). Image replace/remove and
 * activate/inactivate are captured through the `image_path` / `availability` old→new values.
 */
final class ProductObserver
{
    private const FIELDS = [
        'category_id', 'name_ar', 'name_en', 'brand',
        'description_ar', 'description_en', 'image_path', 'availability', 'sort_order',
    ];

    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function created(Product $product): void
    {
        $this->audit->record(AuditAction::CREATED, $product, [], $this->audit->snapshot($product, self::FIELDS));
    }

    public function updated(Product $product): void
    {
        [$old, $new] = $this->audit->changes($product, self::FIELDS);

        if ($new === []) {
            return;
        }

        $this->audit->record(AuditAction::UPDATED, $product, $old, $new);
    }

    public function deleted(Product $product): void
    {
        $this->audit->record(AuditAction::DELETED, $product, $this->audit->snapshot($product, self::FIELDS), []);
    }
}
