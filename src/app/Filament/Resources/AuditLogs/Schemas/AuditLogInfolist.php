<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use App\Domain\Audit\AuditAction;
use App\Domain\Audit\AuditDiffFormatter;
use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\AdminAuditLog;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Read-only audit detail (prompt 39 §12): actor/action/entity plus a human-readable "field:
 * old ← new" change list — never raw JSON as the only view.
 */
class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('تفاصيل السجل')
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i'),
                    TextEntry::make('user.name')->label('المستخدم')->placeholder('النظام'),
                    TextEntry::make('action')->label('الإجراء')->badge()
                        ->formatStateUsing(fn (string $state): string => AuditAction::label($state)),
                    TextEntry::make('auditable_type')->label('النوع')
                        ->formatStateUsing(fn (?string $state): string => AuditLogResource::typeLabel($state)),
                    TextEntry::make('auditable_id')->label('السجل')->placeholder('—'),
                    TextEntry::make('ip_address')->label('عنوان IP')->placeholder('—'),
                ]),
            Section::make('ملخص التغيير')
                ->schema([
                    TextEntry::make('changes')->hiddenLabel()->placeholder('—')
                        ->state(fn (AdminAuditLog $record): array => AuditDiffFormatter::lines($record))
                        ->listWithLineBreaks(),
                ]),
            Section::make('بيانات إضافية')
                ->visible(fn (AdminAuditLog $record): bool => ! empty($record->metadata))
                ->schema([
                    KeyValueEntry::make('metadata')->hiddenLabel(),
                ]),
        ]);
    }
}
