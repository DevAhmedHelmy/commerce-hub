<?php

namespace App\Filament\Resources\Users\Tables;

use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                TextColumn::make('email')->label('البريد الإلكتروني')->searchable(),
                TextColumn::make('roles.name')->label('الأدوار')->badge()
                    ->formatStateUsing(fn (string $state): string => RolesAndPermissionsSeeder::ROLE_LABELS[$state] ?? $state),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
