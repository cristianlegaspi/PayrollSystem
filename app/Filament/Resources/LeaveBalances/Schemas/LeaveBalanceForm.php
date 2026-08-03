<?php

namespace App\Filament\Resources\LeaveBalances\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeaveBalanceForm
{

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_id')
                    ->required()
                    ->numeric(),
                TextInput::make('year')
                    ->required(),
                TextInput::make('annual_credit')
                    ->required()
                    ->numeric()
                    ->default(5.0),
                TextInput::make('used_credit')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('remaining_credit')
                    ->required()
                    ->numeric()
                    ->default(5.0),
            ]);
    }
}
