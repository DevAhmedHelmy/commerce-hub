<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Domain\Inventory\AdjustInventoryAction;
use App\Domain\Inventory\InsufficientStockException;
use App\Domain\Support\Enums\UnitCode;
use App\Domain\Support\Money;
use App\Domain\Support\MoneyFormatter;
use App\Models\ProductUnit;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Selling units + simple inventory (T044c / T148). The create/edit form deliberately
 * excludes `stock_quantity` — stock is changed only through the stock actions, which go
 * via InventoryService and write the audit history (BR-016). Price is entered in EGP and
 * stored as integer minor units.
 */
class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('code')->label('الكود')
                ->options(collect(UnitCode::cases())
                    ->mapWithKeys(fn (UnitCode $c) => [$c->value => __($c->labelKey())])->all())
                ->required(),
            TextInput::make('display_name_ar')->label('الاسم (عربي)')->required()->maxLength(255),
            TextInput::make('display_name_en')->label('الاسم (إنجليزي)')->maxLength(255),
            TextInput::make('package_description_ar')->label('وصف العبوة (عربي)')->maxLength(255),
            TextInput::make('package_description_en')->label('وصف العبوة (إنجليزي)')->maxLength(255),
            TextInput::make('base_price')->label('السعر (ج)')->numeric()->required()->minValue(0)
                ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                ->dehydrateStateUsing(fn ($state) => (int) round((float) $state * 100)),
            Toggle::make('is_active')->label('نشط')->default(true),
            Toggle::make('is_default')->label('الوحدة الافتراضية'),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('display_name_ar')
            ->columns([
                TextColumn::make('display_name_ar')->label('الوحدة')->searchable(),
                TextColumn::make('base_price')->label('السعر')
                    ->formatStateUsing(fn ($state) => MoneyFormatter::format(Money::fromMinor((int) $state))),
                TextColumn::make('stock_quantity')->label(__('inventory.stock'))->badge()
                    ->color(fn (ProductUnit $record) => match (true) {
                        (int) $record->stock_quantity === 0 => 'danger',
                        $record->low_stock_threshold !== null && $record->stock_quantity <= $record->low_stock_threshold => 'warning',
                        default => 'success',
                    }),
                IconColumn::make('is_active')->label('نشط')->boolean(),
                IconColumn::make('is_default')->label('افتراضي')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('in_stock')->label(__('inventory.stock'))
                    ->trueLabel(__('inventory.in_stock'))->falseLabel(__('inventory.out_of_stock'))
                    ->queries(
                        true: fn ($q) => $q->where('stock_quantity', '>', 0),
                        false: fn ($q) => $q->where('stock_quantity', 0),
                        blank: fn ($q) => $q,
                    ),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                $this->adjustStockAction(),
                $this->historyAction(),
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
                TextInput::make('quantity')->label(__('inventory.fields.quantity'))->numeric()->required()->minValue(0),
                Textarea::make('reason')->label(__('inventory.fields.reason'))->rows(2),
            ])
            ->action(function (array $data, ProductUnit $record): void {
                $action = app(AdjustInventoryAction::class);
                $by = auth()->id();
                $qty = (int) $data['quantity'];
                $reason = $data['reason'] ?? null;

                try {
                    match ($data['type']) {
                        'manual_add' => $action->add($record, $qty, $reason, $by),
                        'manual_remove' => $action->remove($record, $qty, $reason, $by),
                        'correction' => $action->correctTo($record, $qty, $reason, $by),
                    };
                    Notification::make()->title('تم تحديث المخزون')->success()->send();
                } catch (InsufficientStockException) {
                    Notification::make()->title(__('inventory.errors.negative'))->danger()->send();
                }
            });
    }

    private function historyAction(): Action
    {
        return Action::make('history')->label(__('inventory.actions.history'))->icon('heroicon-o-clock')
            ->modalHeading(__('inventory.actions.history'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('إغلاق')
            ->modalContent(fn (ProductUnit $record) => view('filament.inventory-history', [
                'adjustments' => $record->adjustments()->with('performedBy')->latest('id')->limit(50)->get(),
            ]));
    }
}
