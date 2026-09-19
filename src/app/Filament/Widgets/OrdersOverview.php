<?php

namespace App\Filament\Widgets;

use App\Domain\Support\Enums\OrderStatus;
use App\Domain\Support\Money;
use App\Domain\Support\MoneyFormatter;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/**
 * Dashboard operational overview (A02, US6): new orders, today's orders, today's sales. Efficient
 * aggregate queries; surfaced without realtime (Filament polling).
 */
class OrdersOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('orders.view');
    }

    protected function getStats(): array
    {
        $today = Carbon::now()->startOfDay();

        $newCount = Order::query()->where('status', OrderStatus::New->value)->count();
        $todayCount = Order::query()->where('created_at', '>=', $today)->count();
        $todaySales = (int) Order::query()
            ->where('created_at', '>=', $today)
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->sum('final_total');

        return [
            Stat::make('طلبات جديدة', (string) $newCount),
            Stat::make('طلبات اليوم', (string) $todayCount),
            Stat::make('مبيعات اليوم', MoneyFormatter::format(Money::fromMinor($todaySales))),
        ];
    }
}
