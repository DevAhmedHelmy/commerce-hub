<?php

namespace App\Filament\Resources\LandingSections;

use App\Filament\Resources\LandingSections\Pages\EditLandingSection;
use App\Filament\Resources\LandingSections\Pages\ListLandingSections;
use App\Filament\Resources\LandingSections\RelationManagers\ItemsRelationManager;
use App\Models\LandingSection;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Landing sections management (prompt 46 §14-§16): activate/deactivate, title/subtitle, order,
 * and section-specific limits. Section keys are stable (not created/deleted here) to protect page
 * structure. Gated by landing permissions.
 */
class LandingSectionResource extends Resource
{
    protected static ?string $model = LandingSection::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationLabel = 'أقسام الصفحة الرئيسية';

    protected static string|\UnitEnum|null $navigationGroup = 'إدارة الصفحة الرئيسية';

    protected static ?string $modelLabel = 'قسم';

    protected static ?string $pluralModelLabel = 'أقسام الصفحة الرئيسية';

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('landing.view');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('landing.manage');
    }

    public static function canCreate(): bool
    {
        return false; // section keys are stable — protect page structure
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->label('المعرّف')->disabled(),
            TextInput::make('title_ar')->label('العنوان')->maxLength(255),
            TextInput::make('subtitle_ar')->label('العنوان الفرعي')->maxLength(255),
            Textarea::make('content_ar')->label('المحتوى')->rows(2),
            TextInput::make('settings.limit')->label('حد العرض (للأقسام الديناميكية)')->numeric()->minValue(1)->maxValue(24),
            Toggle::make('is_active')->label('نشط')->default(true),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label('المعرّف'),
                TextColumn::make('title_ar')->label('العنوان'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
                TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [ItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLandingSections::route('/'),
            'edit' => EditLandingSection::route('/{record}/edit'),
        ];
    }
}
