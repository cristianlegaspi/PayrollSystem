<?php

namespace App\Filament\Resources\CashAdvances\Schemas;

use App\Models\Branch;
use App\Models\Employee;
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
                    ->description('Select branch first, then select the employee under that branch.')
                    ->schema([
                        TextInput::make('ca_no')
                            ->label('C.A No.')
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
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record?->employee?->branch_id) {
                                    $component->state($record->employee->branch_id);
                                }
                            })
                            ->afterStateUpdated(function ($set) {
                                $set('employee_id', null);
                            }),

                        Select::make('employee_id')
                            ->label('Employee')
                            ->options(function ($get) {
                                $branchId = $get('branch_id');

                                if (! $branchId) {
                                    return [];
                                }

                                return Employee::query()
                                    ->where('branch_id', $branchId)
                                    ->orderBy('full_name')
                                    ->get()
                                    ->mapWithKeys(fn ($employee) => [
                                        $employee->id => $employee->display_name,
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($get) => blank($get('branch_id')))
                            ->helperText('Please select a branch first.'),

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
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}