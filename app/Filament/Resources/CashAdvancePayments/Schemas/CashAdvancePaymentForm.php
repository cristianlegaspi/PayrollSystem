<?php

namespace App\Filament\Resources\CashAdvancePayments\Schemas;

use App\Models\CashAdvance;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CashAdvancePaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment / Deduction Details')
                    ->description('Record payments, deductions, or balance reductions for a selected cash advance.')
                    ->schema([
                        TextInput::make('payment_no')
                            ->label('Payment No.')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Automatically generated upon saving.'),

                        Select::make('employee_id')
                            ->label('Employee')
                            ->options(
                                Employee::query()
                                    ->orderBy('full_name')
                                    ->get()
                                    ->mapWithKeys(fn ($employee) => [
                                        $employee->id => $employee->display_name,
                                    ])
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('cash_advance_id', null))
                            ->required(),

                        Select::make('cash_advance_id')
                            ->label('Cash Advance Number')
                            ->options(function ($get) {
                                $employeeId = $get('employee_id');

                                if (! $employeeId) {
                                    return [];
                                }

                                return CashAdvance::query()
                                    ->where('employee_id', $employeeId)
                                    ->latest('transaction_date')
                                    ->get()
                                    ->filter(fn ($cashAdvance) => $cashAdvance->balance > 0)
                                    ->mapWithKeys(fn ($cashAdvance) => [
                                        $cashAdvance->id => $cashAdvance->ca_no
                                            . ' - '
                                            . $cashAdvance->transaction_date->format('M d, Y')
                                            . ' - Balance: ₱'
                                            . number_format($cashAdvance->balance, 2),
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('payment_date')
                            ->label('Date')
                            ->default(now())
                            ->required(),

                        Select::make('payment_type')
                            ->label('Payment Type')
                            ->options([
                                'payment' => 'Payment',
                                'deduction' => 'Deduction',
                              
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