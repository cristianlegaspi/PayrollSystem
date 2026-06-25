<?php

namespace App\Filament\Resources\CashAdvancePayments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CashAdvancePaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('payment_no'),
                TextEntry::make('employee_id')
                    ->numeric(),
                TextEntry::make('cash_advance_id')
                    ->numeric(),
                TextEntry::make('payment_date')
                    ->date(),
                TextEntry::make('payment_type')
                    ->badge(),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('remarks')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
