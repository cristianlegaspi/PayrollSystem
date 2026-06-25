<?php

namespace App\Filament\Resources\CashAdvancePayments\Schemas;

use App\Models\Branch;
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
                    ->description('Record payments for employees with existing cash advance balance.')
                    ->schema([
                        TextInput::make('payment_no')
                            ->label('Payment No.')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Automatically generated upon saving.'),

                        Select::make('branch_id')
                            ->label('Branch')
                            ->options(fn () => Branch::query()
                                ->orderBy('branch_name')
                                ->pluck('branch_name', 'id')
                                ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record?->employee?->branch_id) {
                                    $component->state($record->employee->branch_id);
                                }
                            })
                            ->afterStateUpdated(function ($set) {
                                $set('employee_id', null);
                                $set('cash_advance_id', null);
                            }),

                        Select::make('employee_id')
                            ->label('Employee')
                            ->options(function ($get, $record) {
                                $branchId = $get('branch_id');

                                if (! $branchId) {
                                    return [];
                                }

                                return Employee::query()
                                    ->where('branch_id', $branchId)
                                    ->orderBy('full_name')
                                    ->get()
                                    ->filter(function ($employee) use ($record) {
                                        /*
                                         * Show employee only if:
                                         * 1. Employee has outstanding CA balance, OR
                                         * 2. This is the currently selected employee during edit.
                                         */
                                        return $employee->cash_advance_balance > 0
                                            || $employee->id === $record?->employee_id;
                                    })
                                    ->mapWithKeys(fn ($employee) => [
                                        $employee->id => $employee->display_name
                                            . ' - Balance: ₱'
                                            . number_format($employee->cash_advance_balance, 2),
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->disabled(fn ($get) => blank($get('branch_id')))
                            ->helperText('Only employees with existing C.A balance will appear.')
                            ->afterStateUpdated(function ($set) {
                                $set('cash_advance_id', null);
                            }),

                        Select::make('cash_advance_id')
                            ->label('Cash Advance Number')
                            ->options(function ($get, $record) {
                                $employeeId = $get('employee_id');

                                if (! $employeeId) {
                                    return [];
                                }

                                return CashAdvance::query()
                                    ->where('employee_id', $employeeId)
                                    ->latest('transaction_date')
                                    ->get()
                                    ->filter(function ($cashAdvance) use ($record) {
                                        /*
                                         * Show only unpaid/partial CA records.
                                         * Also include the current selected CA during edit.
                                         */
                                        return $cashAdvance->balance > 0
                                            || $cashAdvance->id === $record?->cash_advance_id;
                                    })
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
                            ->required()
                            ->disabled(fn ($get) => blank($get('employee_id')))
                            ->helperText('Only cash advances with remaining balance will appear.'),

                        DatePicker::make('payment_date')
                            ->label('Date')
                            ->default(now())
                            ->required(),

                        Select::make('payment_type')
                            ->label('Payment Type')
                            ->options([
                                'payment' => 'Payment',
                            ])
                            ->default('payment')
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
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}