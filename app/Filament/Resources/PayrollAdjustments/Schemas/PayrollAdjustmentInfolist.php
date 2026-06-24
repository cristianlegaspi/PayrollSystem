<?php

namespace App\Filament\Resources\PayrollAdjustments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PayrollAdjustmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('employee_id')
                    ->numeric(),
                TextEntry::make('payroll_period_id')
                    ->numeric(),
                TextEntry::make('cash_advance')
                    ->numeric(),
                TextEntry::make('shortages')
                    ->numeric(),
                TextEntry::make('other_deduction')
                    ->numeric(),
                TextEntry::make('other_incentives')
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
