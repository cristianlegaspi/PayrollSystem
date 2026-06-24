<?php

namespace App\Filament\Resources\PayrollAdjustments\Tables;

use App\Models\PayrollAdjustment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayrollAdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_name')
                    ->label('Employee')
                    ->state(function (PayrollAdjustment $record): string {
                        $employee = $record->employee;

                        if (! $employee) {
                            return '-';
                        }

                        return $employee->name
                            ?? $employee->full_name
                            ?? trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''))
                            ?: 'Employee #' . $employee->id;
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payroll_period_description')
                    ->label('Payroll Period')
                    ->state(function (PayrollAdjustment $record): string {
                        $period = $record->payrollPeriod;

                        if (! $period) {
                            return '-';
                        }

                        return $period->description
                            ?? $period->name
                            ?? $period->period_name
                            ?? 'Payroll Period #' . $period->id;
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cash_advance')
                    ->label('Cash Advance')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('shortages')
                    ->label('Shortages')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('other_deduction')
                    ->label('Other Deduction')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('other_incentives')
                    ->label('Other Incentives')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}