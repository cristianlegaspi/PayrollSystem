<?php

namespace App\Filament\Resources\Payrolls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayrollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->whereHas('payrollPeriod', function ($q) {
                    $q->where('status', 'Finalized')
                      ->where('remarks', 'Pending');
                });
            })
            ->columns([
                TextColumn::make('employee.full_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payrollPeriod.description')
                    ->label('Payroll Period')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('days_worked')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('days_absent')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('undertime_hours')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('overtime_hours')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('night_diff_hours')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('night_diff_ot_hours')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('daily_rate')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('basic_salary')
                    ->numeric()
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('overtime_salary')
                    ->label('Regular Overtime Salary')
                    ->numeric()
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('night_diff_salary')
                    ->numeric()
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('night_diff_ot_salary')
                    ->numeric()
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('undertime_deduction')
                    ->numeric()
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('gross_pay')
                    ->numeric()
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('total_deductions')
                    ->label('Contribution')
                    ->numeric()
                    ->color('danger')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('cash_advance')
                    ->numeric()
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('shortages')
                    ->numeric()
                    ->badge()
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('other_deduction')
                    ->numeric()
                    ->badge()
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('other_incentives')
                    ->numeric()
                    ->badge()
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('net_pay')
                    ->numeric()
                    ->badge()
                    ->color('success')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),

                // Prevent editing finalized payroll
                EditAction::make()
                    ->visible(fn ($record) => $record->payrollPeriod?->status !== 'Finalized'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])
                    ->visible(fn () => in_array(auth()->user()->role->role_name, ['Admin', 'Super Admin'])),
            ]);
    }
}