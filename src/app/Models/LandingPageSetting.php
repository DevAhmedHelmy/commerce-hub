<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Support\Concerns\HasLocalizedText;
use Illuminate\Database\Eloquent\Model;

/**
 * Singleton landing-page settings (prompt 46): hero, contact, socials, CTA label. Admin-editable;
 * canonical for landing chrome. Not a CMS — a controlled configuration record.
 */
class LandingPageSetting extends Model
{
    use HasLocalizedText;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** The single settings row (created on demand). */
    public static function singleton(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}
