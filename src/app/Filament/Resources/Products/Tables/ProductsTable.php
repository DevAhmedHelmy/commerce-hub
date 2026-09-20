<?php

namespace App\Filament\Resources\Products\Tables;

use App\Domain\Support\Enums\AvailabilityStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ar')->label('الاسم')->searchable()->sortable(),
                TextColumn::make('brand')->label('الماركة')->searchable(),
                TextColumn::make('category.name_ar')->label('التصنيف')->sortable(),
                // Inline availability quick-toggle (A08). Stock-driven out-of-stock is separate.
                SelectColumn::make('availability')->label('الحالة')
                    ->options(collect(AvailabilityStatus::cases())
                        ->mapWithKeys(fn (AvailabilityStatus $c) => [$c->value => __($c->labelKey())])->all()),
                TextColumn::make('units_count')->counts('units')->label('وحدات البيع'),
            ])
            ->filters([
                SelectFilter::make('availability')->label('الحالة')
                    ->options(collect(AvailabilityStatus::cases())
                        ->mapWithKeys(fn (AvailabilityStatus $c) => [$c->value => __($c->labelKey())])->all()),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
