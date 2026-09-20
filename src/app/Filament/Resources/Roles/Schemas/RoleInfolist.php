<?php

namespace App\Filament\Resources\Roles\Schemas;

use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الدور')->columns(2)->schema([
                TextEntry::make('name')->label('المعرّف')->badge(),
                TextEntry::make('label')->label('الاسم')
                    ->state(fn (Role $record): string => RolesAndPermissionsSeeder::ROLE_LABELS[$record->name] ?? $record->name),
            ]),
            Section::make('الصلاحيات')->schema([
                TextEntry::make('permissions')->hiddenLabel()->placeholder('—')
                    ->state(fn (Role $record): array => $record->permissions->pluck('name')->all())
                    ->badge()->listWithLineBreaks(),
            ]),
        ]);
    }
}
