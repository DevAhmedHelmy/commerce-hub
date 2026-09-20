<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Domain\Access\SuperAdminGuard;
use App\Models\User;
use Closure;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('الاسم')->required()->maxLength(255),
            TextInput::make('email')->label('البريد الإلكتروني')->email()->required()->maxLength(255)
                ->unique(ignoreRecord: true),
            TextInput::make('password')->label('كلمة المرور')->password()->revealable()
                ->minLength(8)
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                ->helperText('اتركها فارغة عند التعديل للإبقاء على كلمة المرور الحالية.'),
            Select::make('roles')->label('الأدوار')
                ->relationship('roles', 'name')->multiple()->preload()
                ->getOptionLabelFromRecordUsing(fn (Role $record): string => RolesAndPermissionsSeeder::ROLE_LABELS[$record->name] ?? $record->name)
                // Last-super-admin protection (prompt 40 §17): cannot strip the role from the last active one.
                ->rule(fn (?User $record): Closure => function (string $attribute, $value, Closure $fail) use ($record): void {
                    if ($record === null) {
                        return;
                    }
                    $names = Role::query()->whereIn('id', (array) $value)->pluck('name')->all();
                    if (app(SuperAdminGuard::class)->isLastActiveSuperAdmin($record) && ! in_array(SuperAdminGuard::ROLE, $names, true)) {
                        $fail('لا يمكن إزالة دور مدير النظام من آخر مدير نشط.');
                    }
                }),
            Toggle::make('is_active')->label('نشط')->default(true),
        ]);
    }
}
