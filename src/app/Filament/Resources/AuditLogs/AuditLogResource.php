<?php

namespace App\Filament\Resources\AuditLogs;

use App\Filament\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Filament\Resources\AuditLogs\Pages\ViewAuditLog;
use App\Filament\Resources\AuditLogs\Schemas\AuditLogInfolist;
use App\Filament\Resources\AuditLogs\Tables\AuditLogsTable;
use App\Models\AdminAuditLog;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Setting;
use App\Models\Unit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only admin audit trail (prompt 39 §11). Append-only: the panel exposes no create, edit,
 * or delete — only list + view. Authorization rides the admin panel (customers are on a separate
 * guard and never reach `/admin`).
 */
class AuditLogResource extends Resource
{
    protected static ?string $model = AdminAuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'سجل التدقيق';

    protected static ?string $modelLabel = 'سجل تدقيق';

    protected static ?string $pluralModelLabel = 'سجل التدقيق';

    public static function table(Table $table): Table
    {
        return AuditLogsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AuditLogInfolist::configure($schema);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    /** Arabic label for an audited entity's morph/class type. */
    public static function typeLabel(?string $class): string
    {
        return match ($class) {
            Product::class => 'منتج',
            ProductUnit::class => 'وحدة منتج',
            Unit::class => 'وحدة',
            Setting::class => 'إعداد',
            null => '—',
            default => class_basename($class),
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogs::route('/'),
            'view' => ViewAuditLog::route('/{record}'),
        ];
    }
}
