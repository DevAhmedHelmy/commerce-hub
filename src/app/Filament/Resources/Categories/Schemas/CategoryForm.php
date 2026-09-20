<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_ar')->label('الاسم (عربي)')->required()->maxLength(255),
                TextInput::make('name_en')->label('الاسم (إنجليزي)')->maxLength(255),
                TextInput::make('slug')->label('المعرّف')->maxLength(255),
                Textarea::make('description_ar')->label('الوصف (عربي)')->rows(3),
                Textarea::make('description_en')->label('الوصف (إنجليزي)')->rows(3),
                Toggle::make('is_active')->label('نشط')->default(true),
                TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
            ]);
    }
}
