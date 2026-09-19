<?php

namespace App\Filament\Resources\DeliverySlots;

use App\Filament\Resources\DeliverySlots\Pages\CreateDeliverySlot;
use App\Filament\Resources\DeliverySlots\Pages\EditDeliverySlot;
use App\Filament\Resources\DeliverySlots\Pages\ListDeliverySlots;
use App\Models\DeliverySlot;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Recurring weekday delivery slots (A12, no capacity). Gated by delivery permissions. */
class DeliverySlotResource extends Resource
{
    protected static ?string $model = DeliverySlot::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'مواعيد التوصيل';

    protected static ?string $modelLabel = 'موعد توصيل';

    protected static ?string $pluralModelLabel = 'مواعيد التوصيل';

    /** ISO 1=Mon..7=Sun → Arabic day names. */
    public const DAYS = [
        1 => 'الاثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء', 4 => 'الخميس',
        5 => 'الجمعة', 6 => 'السبت', 7 => 'الأحد',
    ];

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('delivery.view');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('delivery.manage_slots');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('delivery.manage_slots');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('delivery.manage_slots');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label_ar')->label('التسمية (عربي)')->required()->maxLength(255),
            TextInput::make('label_en')->label('التسمية (إنجليزي)')->maxLength(255),
            Select::make('day_of_week')->label('اليوم')->required()->options(self::DAYS),
            TimePicker::make('start_time')->label('من')->seconds(false)->required(),
            TimePicker::make('end_time')->label('إلى')->seconds(false)->required()->after('start_time'),
            Toggle::make('is_active')->label('نشط')->default(true),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label_ar')->label('التسمية')->searchable(),
                TextColumn::make('day_of_week')->label('اليوم')
                    ->formatStateUsing(fn ($state) => self::DAYS[(int) $state] ?? $state),
                TextColumn::make('start_time')->label('من'),
                TextColumn::make('end_time')->label('إلى'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliverySlots::route('/'),
            'create' => CreateDeliverySlot::route('/create'),
            'edit' => EditDeliverySlot::route('/{record}/edit'),
        ];
    }
}
