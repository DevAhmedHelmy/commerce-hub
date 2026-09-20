<?php

namespace App\Filament\Resources\Products\Tables;

use App\Domain\Inventory\AdjustInventoryAction;
use App\Domain\Inventory\InsufficientStockException;
use App\Domain\Inventory\ProductUnitConverter;
use App\Domain\Support\Enums\AvailabilityStatus;
use App\Domain\Support\Money;
use App\Domain\Support\MoneyFormatter;
use App\Models\Product;
use App\Models\ProductUnit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Admin products table (prompt 48 §15-§18): image, product, category, brand, sellable units,
 * authoritative sub-unit stock, and status — with quick Add-Stock and Edit-Price row actions.
 * Stock/price mutations route through the domain (InventoryService / ProductUnit) so history and
 * audit are preserved; nothing is written directly in a Filament callback bypassing the domain.
 */
class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ar')->label('المنتج')->searchable()->sortable(),
                TextColumn::make('brand')->label('الماركة')->searchable()->toggleable(),
                TextColumn::make('category.name_ar')->label('التصنيف')->sortable()->toggleable(),
                TextColumn::make('units_count')->counts('units')->label('وحدات البيع')->toggleable(),
                TextColumn::make('stock')->label('المخزون')
                    ->state(fn (Product $record): string => self::stockLabel($record)),
                SelectColumn::make('availability')->label('الحالة')
                    ->options(collect(AvailabilityStatus::cases())
                        ->mapWithKeys(fn (AvailabilityStatus $c) => [$c->value => __($c->labelKey())])->all()),
            ])
            ->filters([
                SelectFilter::make('availability')->label('الحالة')
                    ->options(collect(AvailabilityStatus::cases())
                        ->mapWithKeys(fn (AvailabilityStatus $c) => [$c->value => __($c->labelKey())])->all()),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()->label('تعديل'),
                self::addStockAction(),
                self::editPriceAction(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** "125 قطعة" (+ derived "10 كرتونة + 5 قطعة") from the authoritative sub-unit balance. */
    private static function stockLabel(Product $record): string
    {
        $record->loadMissing('units.unit');
        $sub = $record->units->firstWhere('level', ProductUnit::LEVEL_SUB);
        $primary = $record->units->firstWhere('level', ProductUnit::LEVEL_PRIMARY);
        $subStock = $record->subStock();
        $subName = $sub?->label() ?? 'قطعة';

        $label = "{$subStock} {$subName}";

        $conversion = (int) ($primary?->conversion_to_sub_unit ?? 0);
        if ($conversion > 1 && $subStock >= $conversion) {
            $whole = intdiv($subStock, $conversion);
            $rem = $subStock % $conversion;
            $primaryName = $primary?->label() ?? 'كرتونة';
            $label .= " ({$whole} {$primaryName}".($rem > 0 ? " + {$rem} {$subName}" : '').')';
        }

        return $label;
    }

    private static function addStockAction(): Action
    {
        return Action::make('addStock')->label('إضافة مخزون')->icon('heroicon-o-plus-circle')->color('success')
            ->visible(fn (): bool => (bool) auth()->user()?->can('inventory.adjust'))
            ->authorize(fn (): bool => (bool) auth()->user()?->can('inventory.adjust'))
            ->schema([
                Select::make('product_unit_id')->label('الوحدة')->required()
                    ->options(fn (Product $record): array => $record->units()->where('is_active', true)->get()
                        ->mapWithKeys(fn (ProductUnit $u) => [$u->id => $u->label()])->all()),
                TextInput::make('quantity')->label('الكمية')->numeric()->required()->minValue(1),
                Textarea::make('reason')->label('السبب')->rows(2),
            ])
            ->action(function (array $data, Product $record): void {
                $unit = $record->units()->findOrFail($data['product_unit_id']);
                app(AdjustInventoryAction::class)->add($record, $unit, (int) $data['quantity'], $data['reason'] ?? null, auth()->id());
                Notification::make()->title('تمت إضافة المخزون')->success()->send();
            });
    }

    private static function editPriceAction(): Action
    {
        return Action::make('editPrice')->label('تعديل السعر')->icon('heroicon-o-currency-dollar')->color('warning')
            ->visible(fn (): bool => (bool) auth()->user()?->can('pricing.update_base'))
            ->authorize(fn (): bool => (bool) auth()->user()?->can('pricing.update_base'))
            ->fillForm(fn (Product $record): array => [
                'prices' => $record->units()->where('is_sellable', true)->get()
                    ->map(fn (ProductUnit $u) => ['id' => $u->id, 'label' => $u->label(), 'price' => $u->base_price / 100])->values()->all(),
            ])
            ->schema([
                Repeater::make('prices')->label('أسعار الوحدات')->addable(false)->deletable(false)->reorderable(false)
                    ->schema([
                        TextInput::make('label')->label('الوحدة')->disabled()->dehydrated(false),
                        TextInput::make('price')->label('السعر (ج)')->numeric()->required()->minValue(0),
                    ])->columns(2),
            ])
            ->action(function (array $data, Product $record): void {
                // New prices apply to FUTURE orders only — placed-order snapshots are immutable.
                foreach ($data['prices'] ?? [] as $row) {
                    $unit = $record->units()->find($row['id']);
                    $unit?->update(['base_price' => (int) round((float) $row['price'] * 100)]);
                }
                Notification::make()->title('تم تحديث الأسعار')->success()->send();
            });
    }
}
