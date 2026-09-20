<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Models\DeliveryArea;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Admin editing of a customer's delivery addresses (prompt 48 §20). Multi-address-ready; MVP uses a
 * single default. Historical order snapshots are unaffected by later address edits. Gated by
 * customers.update.
 */
class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'العناوين';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return (bool) auth()->user()?->can('customers.view');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('delivery_area_id')->label('منطقة التوصيل')
                ->options(fn () => DeliveryArea::query()->where('is_active', true)->orderBy('sort_order')->pluck('name_ar', 'id')->all())
                ->searchable(),
            TextInput::make('address_line')->label('العنوان')->required()->maxLength(255),
            TextInput::make('building')->label('المبنى')->maxLength(255),
            TextInput::make('floor')->label('الطابق')->maxLength(255),
            TextInput::make('unit')->label('الوحدة')->maxLength(255),
            TextInput::make('landmark')->label('علامة مميزة')->maxLength(255),
            Textarea::make('delivery_notes')->label('ملاحظات التوصيل')->rows(2),
            Toggle::make('is_default')->label('العنوان الافتراضي')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('address_line')
            ->columns([
                TextColumn::make('deliveryArea.name_ar')->label('المنطقة'),
                TextColumn::make('address_line')->label('العنوان'),
                IconColumn::make('is_default')->label('افتراضي')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()->visible(fn (): bool => (bool) auth()->user()?->can('customers.update')),
            ])
            ->recordActions([
                EditAction::make()->visible(fn (): bool => (bool) auth()->user()?->can('customers.update')),
            ]);
    }
}
