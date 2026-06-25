<?php

namespace App\Filament\Resources\CashAdvancePayments;

use App\Filament\Resources\CashAdvancePayments\Pages\CreateCashAdvancePayment;
use App\Filament\Resources\CashAdvancePayments\Pages\EditCashAdvancePayment;
use App\Filament\Resources\CashAdvancePayments\Pages\ListCashAdvancePayments;
use App\Filament\Resources\CashAdvancePayments\Pages\ViewCashAdvancePayment;
use App\Filament\Resources\CashAdvancePayments\Schemas\CashAdvancePaymentForm;
use App\Filament\Resources\CashAdvancePayments\Schemas\CashAdvancePaymentInfolist;
use App\Filament\Resources\CashAdvancePayments\Tables\CashAdvancePaymentsTable;
use App\Models\CashAdvancePayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CashAdvancePaymentResource extends Resource
{
    protected static ?string $model = CashAdvancePayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static ?string $recordTitleAttribute = 'CashAdvancePayment';

    protected static string | UnitEnum | null $navigationGroup = 'Cash Advance Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CashAdvancePaymentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CashAdvancePaymentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashAdvancePaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashAdvancePayments::route('/'),
            'create' => CreateCashAdvancePayment::route('/create'),
            'view' => ViewCashAdvancePayment::route('/{record}'),
            'edit' => EditCashAdvancePayment::route('/{record}/edit'),
        ];
    }
}
