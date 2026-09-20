<?php

declare(strict_types=1);

namespace App\Domain\Support\Concerns;

use App\Domain\Support\LocalizedContent;

/**
 * Model helper for bilingual managed content (R14). For a base attribute `name`,
 * `$model->localized('name')` resolves `name_en ?: name_ar` for the active locale
 * via {@see LocalizedContent}. Keeps locale logic out of models, controllers and
 * Blade. Applied to Category/Product/ProductUnit/etc. in their feature phases.
 */
trait HasLocalizedText
{
    public function localized(string $attribute, ?string $locale = null): string
    {
        return LocalizedContent::resolve(
            $this->getAttribute($attribute.'_ar'),
            $this->getAttribute($attribute.'_en'),
            $locale,
        );
    }
}
