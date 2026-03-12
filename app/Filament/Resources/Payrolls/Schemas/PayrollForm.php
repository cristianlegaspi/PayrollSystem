<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayrollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_id')
                    ->required()
                    ->visible(false)
                    ->numeric(),
                TextInput::make('payroll_period_id')
                    ->required()
                    ->visible(false)
                    ->numeric(),
                TextInput::make('days_worked')
                    ->required()
                    ->visible(false)
                    ->numeric()
                    ->default(0),
                TextInput::make('days_absent')
                    ->required()
                    ->visible(false)
                    ->numeric()
                    ->default(0),
                TextInput::make('undertime_hours')
                    ->required()
                    ->visible(false)
                    ->numeric()
                    ->default(0.0),
                TextInput::make('overtime_hours')
                    ->required()
                    ->visible(false)
                    ->numeric()
                    ->default(0.0),
                TextInput::make('night_diff_hours')
                    ->required()
                    ->visible(false)
                    ->numeric()
                    ->default(0.0),
                TextInput::make('night_diff_ot_hours')
                    ->required()
                    ->visible(false)
                    ->numeric()
                    ->default(0.0),
                TextInput::make('daily_rate')
                    ->required()
                    ->numeric()
                    ->visible(false)
                    ->default(0.0),
                TextInput::make('basic_salary')
                    ->required()
                    ->numeric()
                    ->visible(false)
                    ->default(0.0),
                TextInput::make('overtime_salary')
                    ->required()
                    ->visible(false)
                    ->numeric()
                    ->default(0.0),
                TextInput::make('night_diff_salary')
                    ->required()
                    ->visible(false)
                    ->numeric()
                    ->default(0.0),
                TextInput::make('night_diff_ot_salary')
                    ->required()
                    ->numeric()
                    ->visible(false)
                    ->default(0.0),
                TextInput::make('gross_pay')
                    ->required()
                    ->visible(false)
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_deductions')
                    ->required()
                    ->visible(false)
                    ->numeric()
                    ->default(0.0),
                TextInput::make('cash_advance')
                    ->numeric()
                  
                    ->default(0),
                TextInput::make('shortages')
                    ->numeric()
                    ->default(0),

                TextInput::make('net_pay')
                    ->required()
                    ->numeric()
                    ->visible(false)
                    ->default(0.0),
            ]);
    }
}
