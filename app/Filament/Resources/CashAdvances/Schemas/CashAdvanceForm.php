<?php

namespace App\Filament\Resources\CashAdvances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CashAdvanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cash Advance Details')
                    ->description('Encode cash advance, previous balance, payment, or adjustment per employee.')
                    ->schema([
                        TextInput::make('ca_no')
                            ->label('C.A No.')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Automatically generated upon saving.'),

                        Select::make('employee_id')
                            ->label('Employee')
                            ->relationship('employee', 'full_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('transaction_date')
                            ->label('Date')
                            ->default(now())
                            ->required(),

                     Select::make('type')
                            ->label('Type')
                            ->options([
                                'cash_advance' => 'Cash Advance (CA)',
                                'motor_assistance' => 'Motor Assistance',
                            ])
                            ->required(),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('₱')
                            ->minValue(0.01)
                            ->required(),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}