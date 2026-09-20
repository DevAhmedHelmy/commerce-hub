<?php

namespace App\Filament\Resources\DeliveryDiscountRules;

use App\Domain\Support\Enums\DeliveryDiscountType;
use App\Domain\Support\Money;
use App\Domain\Support\MoneyFormatter;
use App\Filament\Resources\DeliveryDiscountRules\Pages\CreateDeliveryDiscountRule;
use App\Filament\Resources\DeliveryDiscountRules\Pages\EditDeliveryDiscountRule;
use App\Filament\Resources\DeliveryDiscountRules\Pages\ListDeliveryDiscountRules;
use App\Models\DeliveryDiscountRule;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Delivery discount rules (A13). Deterministic largest-saving resolution lives in DeliveryService. */
class DeliveryDiscountRuleResource extends Resource
{
    protected static ?string $model = DeliveryDiscountRule::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'خصومات التوصيل';

    protected static ?string $modelLabel = 'قاعدة خصم توصيل';

    protected static ?string $pluralModelLabel = 'خصومات التوصيل';

    public const TYPES = [
        'fixed' => 'مبلغ ثابت',
        'percentage' => 'نسبة مئوية',
        'free_delivery' => 'توصيل مجاني',
    ];

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('delivery.view');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('delivery.manage_discounts');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('delivery.manage_discounts');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('delivery.manage_discounts');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('الاسم')->required()->maxLength(255)->unique(ignoreRecord: true),
            Select::make('type')->label('النوع')->required()->live()->options(self::TYPES),
            TextInput::make('value')->label('القيمة')->numeric()->default(0)->minValue(0)
                ->helperText('للنسبة المئوية: 0–100. للمبلغ الثابت: بالجنيه. للتوصيل المجاني: تُتجاهل.')
                ->formatStateUsing(fn ($state, callable $get) => $get('type') === 'percentage' ? $state : ($state !== null ? $state / 100 : 0))
                ->dehydrateStateUsing(fn ($state, callable $get) => $get('type') === 'percentage' ? (int) $state : (int) round((float) $state * 100))
                ->rule(fn (callable $get) => $get('type') === 'percentage' ? 'max:100' : null),
            TextInput::make('min_subtotal')->label('الحد الأدنى للطلب (ج)')->numeric()->default(0)->minValue(0)
                ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : 0)
                ->dehydrateStateUsing(fn ($state) => (int) round((float) $state * 100)),
            Toggle::make('is_active')->label('نشط')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('type')->label('النوع')->badge()
                    ->formatStateUsing(fn (DeliveryDiscountType $state) => self::TYPES[$state->value] ?? $state->value),
                TextColumn::make('value')->label('القيمة'),
                TextColumn::make('min_subtotal')->label('الحد الأدنى')
                    ->formatStateUsing(fn ($state) => MoneyFormatter::format(Money::fromMinor((int) $state))),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryDiscountRules::route('/'),
            'create' => CreateDeliveryDiscountRule::route('/create'),
            'edit' => EditDeliveryDiscountRule::route('/{record}/edit'),
        ];
    }
}
