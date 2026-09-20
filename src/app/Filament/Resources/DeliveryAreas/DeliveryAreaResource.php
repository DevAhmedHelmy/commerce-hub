<?php

namespace App\Filament\Resources\DeliveryAreas;

use App\Domain\Support\Money;
use App\Domain\Support\MoneyFormatter;
use App\Filament\Resources\DeliveryAreas\Pages\CreateDeliveryArea;
use App\Filament\Resources\DeliveryAreas\Pages\EditDeliveryArea;
use App\Filament\Resources\DeliveryAreas\Pages\ListDeliveryAreas;
use App\Models\DeliveryArea;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Delivery areas + base fee (A11, prompt 42 §7). Gated by delivery permissions. */
class DeliveryAreaResource extends Resource
{
    protected static ?string $model = DeliveryArea::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'مناطق التوصيل';

    protected static ?string $modelLabel = 'منطقة توصيل';

    protected static ?string $pluralModelLabel = 'مناطق التوصيل';

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('delivery.view');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('delivery.manage_areas');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('delivery.manage_areas');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('delivery.manage_areas');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name_ar')->label('الاسم (عربي)')->required()->maxLength(255),
            TextInput::make('name_en')->label('الاسم (إنجليزي)')->maxLength(255),
            TextInput::make('base_fee')->label('رسوم التوصيل (ج)')->numeric()->required()->minValue(0)
                ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                ->dehydrateStateUsing(fn ($state) => (int) round((float) $state * 100)),
            Toggle::make('is_active')->label('نشط')->default(true),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ar')->label('المنطقة')->searchable()->sortable(),
                TextColumn::make('base_fee')->label('الرسوم')
                    ->formatStateUsing(fn ($state) => MoneyFormatter::format(Money::fromMinor((int) $state))),
                IconColumn::make('is_active')->label('نشط')->boolean(),
                TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryAreas::route('/'),
            'create' => CreateDeliveryArea::route('/create'),
            'edit' => EditDeliveryArea::route('/{record}/edit'),
        ];
    }
}
