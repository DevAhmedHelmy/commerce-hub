<?php

namespace App\Filament\Resources\Orders;

use App\Domain\Support\Enums\OrderStatus;
use App\Domain\Support\Money;
use App\Domain\Support\MoneyFormatter;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Order;
use BackedEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Model;

/**
 * Admin order management (A03/A04, US6). Read + lifecycle actions only — status changes and
 * cancellation route through {@see \App\Domain\Ordering\OrderService} (no logic in the resource).
 */
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'الطلبات';

    protected static ?string $modelLabel = 'طلب';

    protected static ?string $pluralModelLabel = 'الطلبات';

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('orders.view');
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('orders.view');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->label('رقم الطلب')->searchable()->sortable(),
                TextColumn::make('business_name')->label('العميل')->searchable(),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn (OrderStatus $state) => __($state->labelKey())),
                TextColumn::make('final_total')->label('الإجمالي')
                    ->formatStateUsing(fn ($state) => MoneyFormatter::format(Money::fromMinor((int) $state))),
                TextColumn::make('delivery_date')->label('تاريخ التوصيل')->date('Y-m-d'),
                TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($c) => [$c->value => __($c->labelKey())])->all()),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([ViewAction::make()]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الطلب')->columns(2)->schema([
                TextEntry::make('order_number')->label('رقم الطلب'),
                TextEntry::make('status')->label('الحالة')->badge()->formatStateUsing(fn (OrderStatus $state) => __($state->labelKey())),
                TextEntry::make('business_name')->label('العميل'),
                TextEntry::make('phone')->label('الهاتف'),
                TextEntry::make('delivery_area_name')->label('المنطقة'),
                TextEntry::make('address_line')->label('العنوان'),
                TextEntry::make('delivery_date')->label('تاريخ التوصيل')->date('Y-m-d'),
                TextEntry::make('delivery_slot_label')->label('الموعد'),
            ]),
            Section::make('المنتجات')->schema([
                RepeatableEntry::make('items')->hiddenLabel()->schema([
                    TextEntry::make('product_name')->label('المنتج'),
                    TextEntry::make('unit_name')->label('الوحدة'),
                    TextEntry::make('quantity')->label('الكمية'),
                    TextEntry::make('line_total')->label('الإجمالي')
                        ->formatStateUsing(fn ($state) => MoneyFormatter::format(Money::fromMinor((int) $state))),
                ])->columns(4),
            ]),
            Section::make('الإجمالي')->columns(3)->schema([
                TextEntry::make('product_subtotal')->label('إجمالي المنتجات')->formatStateUsing(fn ($state) => MoneyFormatter::format(Money::fromMinor((int) $state))),
                TextEntry::make('final_delivery_fee')->label('رسوم التوصيل')->formatStateUsing(fn ($state) => MoneyFormatter::format(Money::fromMinor((int) $state))),
                TextEntry::make('final_total')->label('الإجمالي')->formatStateUsing(fn ($state) => MoneyFormatter::format(Money::fromMinor((int) $state))),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}
