<?php

namespace App\Filament\Resources\LandingSections\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Repeatable marketing items for a landing section (benefits/steps/highlights) — prompt 46 §10.
 * Never products/categories/offers. `link_url` is validated to safe http(s) schemes.
 */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title_ar')->label('العنوان')->required()->maxLength(255),
            TextInput::make('description_ar')->label('الوصف')->maxLength(255),
            TextInput::make('icon')->label('أيقونة (اختياري)')->maxLength(50),
            TextInput::make('link_url')->label('رابط (اختياري)')->url()->maxLength(255),
            Toggle::make('is_active')->label('نشط')->default(true),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title_ar')
            ->columns([
                TextColumn::make('title_ar')->label('العنوان'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
                TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
