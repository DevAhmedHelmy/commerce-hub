<?php

declare(strict_types=1);

namespace App\Domain\Support;

/**
 * The single source of localized-content resolution for bilingual `*_ar`/`*_en`
 * managed content (R14). Returns the English value for a non-Arabic locale when it
 * is present, otherwise falls back to Arabic (`*_en ?: *_ar`). `*_ar` is always
 * required at the data layer, so this never returns empty for real content.
 *
 * Locale-selection logic MUST NOT be scattered across Blade/controllers/Filament —
 * everything goes through here (or {@see Concerns\HasLocalizedText} on models).
 */
final class LocalizedContent
{
    public static function resolve(?string $arabic, ?string $english, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($locale !== 'ar') {
            $english = trim((string) $english);

            if ($english !== '') {
                return $english;
            }
        }

        return (string) $arabic;
    }
}
