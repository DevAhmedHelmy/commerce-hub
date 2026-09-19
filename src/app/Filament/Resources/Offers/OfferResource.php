<?php

namespace App\Filament\Resources\Offers;

use App\Filament\Resources\Offers\Pages\CreateOffer;
use App\Filament\Resources\Offers\Pages\EditOffer;
use App\Filament\Resources\Offers\Pages\ListOffers;
use App\Domain\Support\Money;
use App\Domain\Support\MoneyFormatter;
use App\Models\ProductOffer;
use App\Models\ProductUnit;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Cross-product offers management (A10, prompt 41 §15). Each offer targets one sellable product
 * unit with a discounted price and validity window. Gated by pricing permissions; changes are
 * audited via {@see \App\Observers\ProductOfferObserver}. Lower-of vs tiers is resolved centrally
 * by PricingService — this screen only configures the offer.
 */
class OfferResource extends Resource
{
    protected static ?string $model = ProductOffer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'العروض';

    protected static ?string $modelLabel = 'عرض';

    protected static ?string $pluralModelLabel = 'العروض';

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('pricing.view');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('pricing.manage_offers');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('pricing.manage_offers');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('pricing.manage_offers');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('product_unit_id')->label('وحدة المنتج')->required()->searchable()
                ->options(fn (): array => ProductUnit::query()->with(['product', 'unit'])->where('is_sellable', true)->get()
                    ->mapWithKeys(fn (ProductUnit $u): array => [$u->id => trim(($u->product?->localized('name') ?? '').' — '.$u->label())])->all()),
            TextInput::make('offer_price')->label('سعر العرض (ج)')->numeric()->required()->minValue(1)
                ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                ->dehydrateStateUsing(fn ($state) => (int) round((float) $state * 100)),
            DateTimePicker::make('starts_at')->label('يبدأ في')->required()->seconds(false),
            DateTimePicker::make('ends_at')->label('ينتهي في')->required()->seconds(false)->after('starts_at'),
            TextInput::make('title_ar')->label('العنوان (عربي)')->maxLength(255),
            Toggle::make('is_active')->label('نشط')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('productUnit.product.name_ar')->label('المنتج')->searchable()->sortable(),
                TextColumn::make('productUnit.level')->label('الوحدة')
                    ->formatStateUsing(fn (string $state): string => $state === ProductUnit::LEVEL_PRIMARY ? 'رئيسية' : 'فرعية'),
                TextColumn::make('offer_price')->label('سعر العرض')
                    ->formatStateUsing(fn ($state) => MoneyFormatter::format(Money::fromMinor((int) $state))),
                TextColumn::make('starts_at')->label('يبدأ')->dateTime('Y-m-d H:i'),
                TextColumn::make('ends_at')->label('ينتهي')->dateTime('Y-m-d H:i'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->filters([
                SelectFilter::make('is_active')->label('الحالة')->options([1 => 'نشط', 0 => 'غير نشط']),
            ])
            ->defaultSort('ends_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOffers::route('/'),
            'create' => CreateOffer::route('/create'),
            'edit' => EditOffer::route('/{record}/edit'),
        ];
    }
}
