<?php

declare(strict_types=1);

namespace App\Domain\Audit;

use App\Domain\Support\Money;
use App\Domain\Support\MoneyFormatter;
use App\Models\AdminAuditLog;

/**
 * Renders an audit record's old/new payload as a readable Arabic diff (prompt 39 §12) —
 * never raw JSON as the only view. Money-like fields render via {@see MoneyFormatter}, booleans
 * and known enums use localized labels, and field keys map to Arabic labels.
 */
final class AuditDiffFormatter
{
    /** @return array<string,string> */
    private static function fieldLabels(): array
    {
        return [
            'name_ar' => 'الاسم (عربي)',
            'name_en' => 'الاسم (إنجليزي)',
            'brand' => 'العلامة التجارية',
            'description_ar' => 'الوصف (عربي)',
            'description_en' => 'الوصف (إنجليزي)',
            'image_path' => 'الصورة',
            'availability' => 'الحالة',
            'category_id' => 'التصنيف',
            'sort_order' => 'الترتيب',
            'code' => 'الكود',
            'is_active' => 'نشط',
            'is_sellable' => 'قابل للبيع',
            'level' => 'المستوى',
            'unit_id' => 'الوحدة',
            'conversion_to_sub_unit' => 'عدد الوحدات الفرعية داخل الرئيسية',
            'base_price' => 'السعر',
            'unit_price' => 'السعر',
            'value' => 'القيمة',
        ];
    }

    /** Money-valued fields are stored in integer minor units and rendered as currency. */
    private static function isMoneyField(string $field): bool
    {
        return str_contains($field, 'price') || str_contains($field, 'amount');
    }

    /**
     * Human-readable "label: old → new" lines for the changed fields.
     *
     * @return list<string>
     */
    public static function lines(AdminAuditLog $log): array
    {
        $labels = self::fieldLabels();
        $old = $log->old_values ?? [];
        $new = $log->new_values ?? [];
        $fields = array_keys($new ?: $old);

        $lines = [];
        foreach ($fields as $field) {
            $label = $labels[$field] ?? $field;
            $before = self::value($field, $old[$field] ?? null);
            $after = self::value($field, $new[$field] ?? null);

            $lines[] = array_key_exists($field, $new) && array_key_exists($field, $old)
                ? "{$label}: {$before} ← {$after}" // RTL: new on the reading-start side
                : "{$label}: {$after}";
        }

        return $lines;
    }

    /** Compact one-line summary for the audit table. */
    public static function summary(AdminAuditLog $log): string
    {
        $lines = self::lines($log);

        if ($lines === []) {
            return '—';
        }

        $summary = implode('، ', array_slice($lines, 0, 2));

        return count($lines) > 2 ? $summary.' …' : $summary;
    }

    private static function value(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (self::isMoneyField($field)) {
            return MoneyFormatter::format(Money::fromMinor((int) $value));
        }

        if (is_bool($value)) {
            return $value ? 'نعم' : 'لا';
        }

        if ($field === 'is_active' || $field === 'is_sellable') {
            return $value ? 'نعم' : 'لا';
        }

        if ($field === 'level') {
            return $value === 'primary' ? 'رئيسية' : 'فرعية';
        }

        return (string) $value;
    }
}
