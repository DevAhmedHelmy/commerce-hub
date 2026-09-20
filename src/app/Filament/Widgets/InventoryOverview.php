<?php

namespace App\Filament\Widgets;

use App\Domain\Support\Enums\AvailabilityStatus;
use App\Models\ProductUnit;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Simple inventory visibility (T154, US6): count of out-of-stock products by authoritative
 * sub-unit balance. No reorder-point logic. Gated by inventory.view.
 */
class InventoryOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('inventory.view');
    }

    protected function getStats(): array
    {
        $outOfStock = ProductUnit::query()
            ->where('level', ProductUnit::LEVEL_SUB)
            ->where('stock_quantity', '<=', 0)
            ->whereHas('product', fn ($q) => $q->where('availability', '!=', AvailabilityStatus::Inactive->value))
            ->count();

        return [
            Stat::make('أصناف غير متوفرة', (string) $outOfStock)
                ->description('حسب رصيد الوحدة الفرعية')
                ->color($outOfStock > 0 ? 'danger' : 'success'),
        ];
    }
}
