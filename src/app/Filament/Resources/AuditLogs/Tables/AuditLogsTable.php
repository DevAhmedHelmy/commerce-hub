<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use App\Domain\Audit\AuditAction;
use App\Domain\Audit\AuditDiffFormatter;
use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\AdminAuditLog;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read-only audit table (prompt 39 §11). Key columns: date, actor, action, entity type/id, and a
 * readable change summary. Filters by actor, action, entity type, and date range. No edit/delete.
 */
class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('user.name')->label('المستخدم')->placeholder('النظام')->searchable(),
                TextColumn::make('action')->label('الإجراء')->badge()
                    ->formatStateUsing(fn (string $state): string => AuditAction::label($state)),
                TextColumn::make('auditable_type')->label('النوع')
                    ->formatStateUsing(fn (?string $state): string => AuditLogResource::typeLabel($state)),
                TextColumn::make('auditable_id')->label('السجل')->placeholder('—'),
                TextColumn::make('summary')->label('ملخص التغيير')->wrap()
                    ->state(fn (AdminAuditLog $record): string => AuditDiffFormatter::summary($record)),
            ])
            ->filters([
                SelectFilter::make('user_id')->label('المستخدم')->relationship('user', 'name'),
                SelectFilter::make('action')->label('الإجراء')->options(AuditAction::labels()),
                SelectFilter::make('auditable_type')->label('النوع')->options([
                    \App\Models\Product::class => 'منتج',
                    \App\Models\ProductUnit::class => 'وحدة منتج',
                    \App\Models\Unit::class => 'وحدة',
                    \App\Models\Setting::class => 'إعداد',
                ]),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->label('من تاريخ'),
                        DatePicker::make('until')->label('إلى تاريخ'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
