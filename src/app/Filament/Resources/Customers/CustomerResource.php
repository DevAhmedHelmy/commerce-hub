<?php

namespace App\Filament\Resources\Customers;

use App\Domain\Support\Enums\OrderStatus;
use App\Domain\Support\Money;
use App\Domain\Support\MoneyFormatter;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Models\Customer;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Customer directory (A05/A06, US11): read-only search + profile with order history. Customers are
 * B2B accounts (separate guard); admins never create/edit them here. Gated by customers.view.
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')->label('اسم النشاط')->searchable()->sortable(),
                TextColumn::make('contact_person_name')->label('مسؤول التواصل')->searchable(),
                TextColumn::make('phone')->label('الهاتف')->searchable(),
                TextColumn::make('orders_count')->counts('orders')->label('الطلبات'),
                TextColumn::make('created_at')->label('العميل منذ')->date('Y-m-d'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([ViewAction::make()]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('العميل')->columns(2)->schema([
                TextEntry::make('business_name')->label('اسم النشاط'),
                TextEntry::make('contact_person_name')->label('مسؤول التواصل'),
                TextEntry::make('phone')->label('الهاتف'),
                TextEntry::make('whatsapp_phone')->label('واتساب'),
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

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'view' => ViewCustomer::route('/{record}'),
        ];
    }
}
