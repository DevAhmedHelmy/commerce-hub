<?php

namespace App\Filament\Resources\Customers;

use App\Domain\Support\Enums\OrderStatus;
use App\Domain\Support\Money;
use App\Domain\Support\MoneyFormatter;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\RelationManagers\AddressesRelationManager;
use App\Models\Customer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Customer directory + admin-only management (A05/A06, prompt 48 §20-§23). Customers are B2B
 * accounts (separate guard); admins edit profile/address here (self-edit is disabled). Activation
 * toggles `is_active` (inactive customers cannot log in). Historical order snapshots are never
 * touched. Gated by customers.view/update/activate.
 */
class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'العملاء';

    protected static ?string $modelLabel = 'عميل';

    protected static ?string $pluralModelLabel = 'العملاء';

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('customers.view');
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('customers.view');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('customers.update');
    }

    public static function canCreate(): bool
    {
        return false; // customers self-register via OTP
    }

    public static function canDelete(Model $record): bool
    {
        return false; // never delete customers (preserve history)
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات العميل')->columns(2)->schema([
                TextInput::make('business_name')->label('اسم النشاط')->required()->maxLength(255),
                TextInput::make('contact_person_name')->label('مسؤول التواصل')->required()->maxLength(255),
                TextInput::make('phone')->label('هاتف الدخول')->required()->maxLength(30)
                    ->unique(ignoreRecord: true)
                    ->helperText('رقم الدخول للعميل — يجب أن يكون فريداً.'),
                TextInput::make('whatsapp_phone')->label('واتساب')->maxLength(30),
                Toggle::make('is_active')->label('الحساب نشط')
                    ->disabled(fn (): bool => ! auth()->user()?->can('customers.activate')),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')->label('اسم النشاط')->searchable()->sortable(),
                TextColumn::make('contact_person_name')->label('مسؤول التواصل')->searchable(),
                TextColumn::make('phone')->label('الهاتف')->searchable(),
                IconColumn::make('is_active')->label('نشط')->boolean(),
                TextColumn::make('orders_count')->counts('orders')->label('الطلبات'),
                TextColumn::make('created_at')->label('العميل منذ')->date('Y-m-d'),
            ])
            ->filters([
                SelectFilter::make('is_active')->label('الحالة')->options([1 => 'نشط', 0 => 'موقوف']),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                self::toggleActiveAction(),
            ]);
    }

    private static function toggleActiveAction(): Action
    {
        return Action::make('toggleActive')
            ->label(fn (Customer $record): string => $record->is_active ? 'تعطيل العميل' : 'تفعيل العميل')
            ->icon(fn (Customer $record): string => $record->is_active ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
            ->color(fn (Customer $record): string => $record->is_active ? 'danger' : 'success')
            ->visible(fn (): bool => (bool) auth()->user()?->can('customers.activate'))
            ->requiresConfirmation()
            ->action(fn (Customer $record) => $record->update(['is_active' => ! $record->is_active]));
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('العميل')->columns(2)->schema([
                TextEntry::make('business_name')->label('اسم النشاط'),
                TextEntry::make('contact_person_name')->label('مسؤول التواصل'),
                TextEntry::make('phone')->label('الهاتف'),
                TextEntry::make('whatsapp_phone')->label('واتساب'),
                TextEntry::make('is_active')->label('الحالة')->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'نشط' : 'موقوف')
                    ->color(fn ($state): string => $state ? 'success' : 'danger'),
            ]),
            Section::make('سجل الطلبات')->schema([
                RepeatableEntry::make('orders')->hiddenLabel()->schema([
                    TextEntry::make('order_number')->label('رقم الطلب'),
                    TextEntry::make('status')->label('الحالة')->badge()
                        ->formatStateUsing(fn (OrderStatus $state) => __($state->labelKey())),
                    TextEntry::make('final_total')->label('الإجمالي')
                        ->formatStateUsing(fn ($state) => MoneyFormatter::format(Money::fromMinor((int) $state))),
                    TextEntry::make('created_at')->label('التاريخ')->dateTime('Y-m-d'),
                ])->columns(4),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [AddressesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'view' => ViewCustomer::route('/{record}'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}
