<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Models\Setting;

/**
 * Audits business-setting changes (prompt 39 §1: minimum-order and other critical commerce
 * settings). Each setting is a key/value row; the changed key is recorded in metadata and the
 * old/new value in the payload.
 */
final class SettingObserver
{
    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function created(Setting $setting): void
    {
        $this->audit->record(
            AuditAction::CREATED,
            $setting,
            [],
            ['value' => $setting->value],
            ['key' => $setting->key],
        );
    }

    public function updated(Setting $setting): void
    {
        if (! $setting->wasChanged('value')) {
            return;
        }

        $this->audit->record(
            AuditAction::UPDATED,
            $setting,
            ['value' => $setting->getOriginal('value')],
            ['value' => $setting->value],
            ['key' => $setting->key],
        );
    }
}
