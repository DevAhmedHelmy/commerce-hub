<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Domain\Support\Enums\AvailabilityStatus;
use App\Domain\Support\MediaService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->tabs([
                Tab::make('عام')->schema([
                    Select::make('category_id')->label('التصنيف')
                        ->relationship('category', 'name_ar')->required()->searchable()->preload(),
                    TextInput::make('name_ar')->label('الاسم (عربي)')->required()->maxLength(255),
                    TextInput::make('name_en')->label('الاسم (إنجليزي)')->maxLength(255),
                    TextInput::make('brand')->label('الماركة')->maxLength(255),
                    Textarea::make('description_ar')->label('الوصف (عربي)')->rows(3),
                    Textarea::make('description_en')->label('الوصف (إنجليزي)')->rows(3),
                    TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                ]),
                Tab::make('الصورة')->schema([
                    FileUpload::make('image_path')->label('صورة المنتج')
                        ->image()->disk('public')->directory('products')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(MediaService::MAX_KILOBYTES),
                ]),
                Tab::make('الإتاحة')->schema([
                    Select::make('availability')->label('الحالة')
                        ->options(collect(AvailabilityStatus::cases())
                            ->mapWithKeys(fn (AvailabilityStatus $c) => [$c->value => __($c->labelKey())])->all())
                        ->required()->default(AvailabilityStatus::Available->value)
                        ->helperText('نفاد المخزون يُحسب تلقائيًا من رصيد كل وحدة بيع.'),
                ]),
            ]),
        ]);
    }
}
