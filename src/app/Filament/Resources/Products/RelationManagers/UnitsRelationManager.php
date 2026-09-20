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
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\DB;
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
                // Conversion-change safety (prompt 37 §24) + RBAC (prompt 40 §12): lock the factor
                // once stock exists, and only users with units.change_conversion may edit an existing one.
                ->disabled(fn (?ProductUnit $record): bool => $record !== null
                    && ($record->product?->subStock() > 0 || ! auth()->user()?->can('units.change_conversion')))
                ->helperText(fn (?ProductUnit $record): string => $record !== null && $record->product?->subStock() > 0
                    ? 'لا يمكن تغيير التحويل أثناء وجود مخزون. صحّح المخزون إلى صفر أولاً.'
                    : 'للوحدة الفرعية اترك القيمة 1.'),
            TextInput::make('base_price')->label('السعر (ج)')->numeric()->required()->minValue(0)
                // RBAC (prompt 40 §13): only users with pricing.update_base may edit prices.
                ->disabled(fn (): bool => ! auth()->user()?->can('pricing.update_base'))
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
                $this->manageTiersAction(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Manage this unit's quantity price tiers (prompt 41 §14). Ascending thresholds are unique per
     * unit; prices are entered in whole EGP and stored as minor units. Gated by pricing.manage_tiers.
     */
    private function manageTiersAction(): Action
    {
        return Action::make('manageTiers')->label('شرائح الكمية')->icon('heroicon-o-bars-3-bottom-left')
            ->visible(fn (): bool => (bool) auth()->user()?->can('pricing.manage_tiers'))
            ->authorize(fn (): bool => (bool) auth()->user()?->can('pricing.manage_tiers'))
            ->fillForm(fn (ProductUnit $record): array => [
                'tiers' => $record->priceTiers()->orderBy('min_quantity')->get()
                    ->map(fn ($t) => [
                        'min_quantity' => $t->min_quantity,
                        'unit_price' => $t->unit_price / 100,
                        'is_active' => $t->is_active,
                    ])->all(),
            ])
            ->schema([
                Repeater::make('tiers')->label('الشرائح')->addActionLabel('إضافة شريحة')->schema([
                    TextInput::make('min_quantity')->label('الحد الأدنى للكمية')->numeric()->required()->minValue(1),
                    TextInput::make('unit_price')->label('سعر الوحدة (ج)')->numeric()->required()->minValue(1),
                    Toggle::make('is_active')->label('نشط')->default(true),
                ])->columns(3)->defaultItems(0),
            ])
            ->action(function (array $data, ProductUnit $record): void {
                DB::transaction(function () use ($data, $record): void {
                    $keep = [];
                    foreach ($data['tiers'] ?? [] as $row) {
                        $min = (int) $row['min_quantity'];
                        $keep[] = $min;
                        $record->priceTiers()->updateOrCreate(
                            ['min_quantity' => $min],
                            ['unit_price' => (int) round((float) $row['unit_price'] * 100), 'is_active' => (bool) ($row['is_active'] ?? true)],
                        );
                    }
                    $record->priceTiers()->when($keep !== [], fn ($q) => $q->whereNotIn('min_quantity', $keep))->delete();
                });
                Notification::make()->title('تم تحديث شرائح السعر')->success()->send();
            });
    }

    private function adjustStockAction(): Action
    {
        return Action::make('adjustStock')->label(__('inventory.actions.add'))->icon('heroicon-o-adjustments-horizontal')
            // RBAC (prompt 40 §14): stock adjustments require inventory.adjust; not hidden-only.
            ->visible(fn (): bool => (bool) auth()->user()?->can('inventory.adjust'))
            ->authorize(fn (): bool => (bool) auth()->user()?->can('inventory.adjust'))
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
