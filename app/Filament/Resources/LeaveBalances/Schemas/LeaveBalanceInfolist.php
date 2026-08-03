<?php

namespace App\Filament\Resources\LeaveBalances\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LeaveBalanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('employee_id')
                    ->numeric(),
                TextEntry::make('year'),
                TextEntry::make('annual_credit')
                    ->numeric(),
                TextEntry::make('used_credit')
                    ->numeric(),
                TextEntry::make('remaining_credit')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
