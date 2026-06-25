<?php

namespace App\Filament\Resources\CashAdvances\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CashAdvanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('ca_no'),
                TextEntry::make('employee_id')
                    ->numeric(),
                TextEntry::make('transaction_date')
                    ->date(),
                TextEntry::make('type')
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
