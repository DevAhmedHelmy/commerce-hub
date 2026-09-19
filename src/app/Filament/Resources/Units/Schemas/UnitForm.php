<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('الكود')->required()->maxLength(50)
                ->helperText('معرّف محايد للغة، مثل carton / piece.'),
            TextInput::make('name_ar')->label('الاسم (عربي)')->required()->maxLength(255),
            TextInput::make('name_en')->label('الاسم (إنجليزي)')->maxLength(255),
            Toggle::make('is_active')->label('نشط')->default(true),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
        ]);
    }
}
