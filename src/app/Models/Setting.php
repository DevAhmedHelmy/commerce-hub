<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Key/value business setting (data-model #17). Read/written through
 * {@see \App\Domain\Settings\SettingsService}, which owns caching and typing.
 */
class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];
}
