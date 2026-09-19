<?php

namespace App\Filament\Resources\Roles\Tables;

use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الدور')
                    ->formatStateUsing(fn (string $state): string => RolesAndPermissionsSeeder::ROLE_LABELS[$state] ?? $state),
                TextColumn::make('name')->label('المعرّف')->badge(),
                TextColumn::make('permissions_count')->counts('permissions')->label('عدد الصلاحيات'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
