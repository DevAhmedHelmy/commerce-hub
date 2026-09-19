<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Domain\Inventory\AdjustInventoryAction;
use App\Domain\Inventory\InsufficientStockException;
use App\Domain\Inventory\ProductUnitConverter;
use App\Domain\Support\Money;
use App\Domain\Support\MoneyFormatter;
use App\Models\ProductUnit;
use App\Models\Unit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Product↔unit configuration (prompt 37/38): each product has a primary + sub unit with a
 * conversion. Stock is NOT edited here directly — the row's stock action routes through
 * InventoryService (converting primary input to the sub-unit balance) and writes history.
 */
class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('unit_id')->label('الوحدة')
                ->options(fn () => Unit::query()->active()->orderBy('sort_order')->pluck('name_ar', 'id')->all())
                ->searchable()->required(),
            Select::make('level')->label('المستوى')->required()->default(ProductUnit::LEVEL_SUB)
                ->options([
                    ProductUnit::LEVEL_PRIMARY => 'الوحدة الرئيسية',
                    ProductUnit::LEVEL_SUB => 'الوحدة الفرعية',
                ]),
            TextInput::make('conversion_to_sub_unit')->label('عدد الوحدات الفرعية داخل الوحدة الرئيسية')
                ->numeric()->minValue(1)->default(1)->required()
                // Conversion-change safety (prompt 37 §24): lock the factor once stock exists so
                // the authoritative sub-unit balance is never reinterpreted under a new factor.
                ->disabled(fn (?ProductUnit $record): bool => $record !== null && $record->product?->subStock() > 0)
                ->helperText(fn (?ProductUnit $record): string => $record !== null && $record->product?->subStock() > 0
                    ? 'لا يمكن تغيير التحويل أثناء وجود مخزون. صحّح المخزون إلى صفر أولاً.'
                    : 'للوحدة الفرعية اترك القيمة 1.'),
            TextInput::make('base_price')->label('السعر (ج)')->numeric()->required()->minValue(0)
                ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                ->dehydrateStateUsing(fn ($state) => (int) round((float) $state * 100)),
            Toggle::make('is_sellable')->label('قابل للبيع')->default(true),
            Toggle::make('is_active')->label('نشط')->default(true),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('level')
            ->columns([
                TextColumn::make('unit.name_ar')->label('الوحدة'),
                TextColumn::make('level')->label('المستوى')->badge()
                    ->formatStateUsing(fn (string $state) => $state === ProductUnit::LEVEL_PRIMARY ? 'رئيسية' : 'فرعية'),
                TextColumn::make('conversion_to_sub_unit')->label('التحويل'),
                TextColumn::make('base_price')->label('السعر')
                    ->formatStateUsing(fn ($state) => MoneyFormatter::format(Money::fromMinor((int) $state))),
                TextColumn::make('stock_quantity')->label(__('inventory.stock'))
                    ->formatStateUsing(fn (ProductUnit $record) => $record->isSub() ? (int) $record->stock_quantity : '—'),
                IconColumn::make('is_sellable')->label('للبيع')->boolean(),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                $this->adjustStockAction(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private function adjustStockAction(): Action
    {
        return Action::make('adjustStock')->label(__('inventory.actions.add'))->icon('heroicon-o-adjustments-horizontal')
            ->schema([
                Select::make('type')->label('نوع التعديل')->required()->default('manual_add')
                    ->options([
                        'manual_add' => __('inventory.actions.add'),
                        'manual_remove' => __('inventory.actions.remove'),
                        'correction' => __('inventory.actions.correct'),
                    ]),
                TextInput::make('quantity')->label(__('inventory.fields.quantity'))->numeric()->required()->minValue(0)
                    ->helperText('بوحدة هذا الصف؛ يتم تحويلها تلقائيًا إلى الوحدة الفرعية.'),
                Textarea::make('reason')->label(__('inventory.fields.reason'))->rows(2),
            ])
            ->action(function (array $data, ProductUnit $record): void {
                $action = app(AdjustInventoryAction::class);
                $converter = app(ProductUnitConverter::class);
                $product = $record->product;
                $qty = (int) $data['quantity'];
                $reason = $data['reason'] ?? null;
                $by = auth()->id();

                try {
                    match ($data['type']) {
                        'manual_add' => $action->add($product, $record, $qty, $reason, $by),
                        'manual_remove' => $action->remove($product, $record, $qty, $reason, $by),
                        'correction' => $action->correctTo($product, $converter->toSubUnits($record, $qty), $reason, $by),
                    };
                    Notification::make()->title('تم تحديث المخزون')->success()->send();
                } catch (InsufficientStockException) {
                    Notification::make()->title(__('inventory.errors.negative'))->danger()->send();
                }
            });
    }
}
