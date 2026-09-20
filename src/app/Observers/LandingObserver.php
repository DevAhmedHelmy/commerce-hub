<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Domain\Landing\LandingPageService;
use Illuminate\Database\Eloquent\Model;

/**
 * Single observer for all landing models (prompt 46 §20/§24): flushes the landing cache on any
 * change and writes an admin audit entry (create/update/activate/deactivate). Registered for
 * LandingPageSetting / LandingSection / LandingSectionItem. Never logs image binary or full dumps.
 */
final class LandingObserver
{
    public function __construct(
        private readonly AdminAuditService $audit,
        private readonly LandingPageService $landing,
    ) {
    }

    public function saved(Model $model): void
    {
        $this->landing->flush();
    }

    public function deleted(Model $model): void
    {
        $this->landing->flush();
    }

    public function created(Model $model): void
    {
        $this->audit->record(AuditAction::CREATED, $model, [], $this->audit->snapshot($model, $this->fields($model)));
    }

    public function updated(Model $model): void
    {
        [$old, $new] = $this->audit->changes($model, $this->fields($model));

        if ($new === []) {
            return;
        }

        if (array_keys($new) === ['is_active']) {
            $action = $model->getAttribute('is_active') ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED;
        } else {
            $action = AuditAction::UPDATED;
        }

        $this->audit->record($action, $model, $old, $new);
    }

    /** @return list<string> audited (non-binary) fields per landing model */
    private function fields(Model $model): array
    {
        return match (class_basename($model)) {
            'LandingPageSetting' => [
                'hero_title_ar', 'hero_subtitle_ar', 'hero_image_path', 'primary_cta_label_ar',
                'phone', 'whatsapp_phone', 'email', 'facebook_url', 'instagram_url', 'tiktok_url', 'is_active',
            ],
            'LandingSection' => ['key', 'title_ar', 'subtitle_ar', 'is_active', 'sort_order', 'settings'],
            'LandingSectionItem' => ['title_ar', 'description_ar', 'icon', 'link_url', 'is_active', 'sort_order'],
            default => ['is_active'],
        };
    }
}
