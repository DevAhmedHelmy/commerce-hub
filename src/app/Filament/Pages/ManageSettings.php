<?php

namespace App\Filament\Pages;

use App\Domain\Settings\SettingsService;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

/**
 * Business settings (A14, prompt 42): minimum order amount + business/contact info via
 * {@see SettingsService}. Gated by settings permissions; changes are audited via SettingObserver.
 */
class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'الإعدادات';

    protected static ?string $title = 'الإعدادات';

    protected string $view = 'filament.pages.manage-settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('settings.view');
    }

    public function mount(): void
    {
        $settings = app(SettingsService::class);

        $this->form->fill([
            'minimum_order_amount' => (int) $settings->get('minimum_order_amount', '0') / 100,
            'business_name_ar' => $settings->get('business_name_ar'),
            'business_phone' => $settings->get('business_phone'),
            'business_whatsapp' => $settings->get('business_whatsapp'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('minimum_order_amount')->label('الحد الأدنى للطلب (ج)')->numeric()->minValue(0)->required(),
                TextInput::make('business_name_ar')->label('اسم النشاط')->maxLength(255),
                TextInput::make('business_phone')->label('هاتف النشاط')->tel()->maxLength(30),
                TextInput::make('business_whatsapp')->label('واتساب النشاط')->tel()->maxLength(30),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        abort_unless((bool) auth()->user()?->can('settings.update'), 403);

        $data = $this->form->getState();

        app(SettingsService::class)->update([
            'minimum_order_amount' => (string) (int) round(((float) $data['minimum_order_amount']) * 100),
            'business_name_ar' => $data['business_name_ar'],
            'business_phone' => $data['business_phone'],
            'business_whatsapp' => $data['business_whatsapp'],
        ]);

        Notification::make()->title('تم حفظ الإعدادات')->success()->send();
    }
}
