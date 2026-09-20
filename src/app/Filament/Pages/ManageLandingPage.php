<?php

namespace App\Filament\Pages;

use App\Models\LandingPageSetting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

/**
 * Landing page general settings (prompt 46 §14): hero, CTA label, contact, socials. A controlled
 * configuration screen — not a CMS. Gated by landing permissions; changes are audited + cache-flushed
 * via LandingObserver. The primary CTA target stays application-controlled (only the label is editable).
 */
class ManageLandingPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-window';

    protected static ?string $navigationLabel = 'الصفحة الرئيسية';

    protected static ?string $title = 'إعدادات الصفحة الرئيسية';

    protected static string|\UnitEnum|null $navigationGroup = 'إدارة الصفحة الرئيسية';

    protected string $view = 'filament.pages.manage-landing-page';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('landing.view');
    }

    public function mount(): void
    {
        $this->form->fill(LandingPageSetting::singleton()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('القسم الرئيسي (Hero)')->columns(2)->schema([
                    TextInput::make('hero_title_ar')->label('العنوان')->maxLength(255),
                    TextInput::make('primary_cta_label_ar')->label('نص زر الطلب')->maxLength(255),
                    TextInput::make('hero_subtitle_ar')->label('العنوان الفرعي')->maxLength(255)->columnSpanFull(),
                    FileUpload::make('hero_image_path')->label('صورة الهيرو')->image()->disk('public')->directory('landing')
                        ->maxSize(4096)->columnSpanFull(),
                ]),
                Section::make('بيانات التواصل')->columns(3)->schema([
                    TextInput::make('phone')->label('الهاتف')->tel()->maxLength(30),
                    TextInput::make('whatsapp_phone')->label('واتساب')->tel()->maxLength(30),
                    TextInput::make('email')->label('البريد')->email()->maxLength(255),
                ]),
                Section::make('روابط التواصل الاجتماعي')->columns(3)->schema([
                    TextInput::make('facebook_url')->label('فيسبوك')->url()->maxLength(255),
                    TextInput::make('instagram_url')->label('إنستغرام')->url()->maxLength(255),
                    TextInput::make('tiktok_url')->label('تيك توك')->url()->maxLength(255),
                ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        abort_unless((bool) auth()->user()?->can('landing.manage'), 403);

        LandingPageSetting::singleton()->update($this->form->getState());

        Notification::make()->title('تم حفظ إعدادات الصفحة الرئيسية')->success()->send();
    }
}
