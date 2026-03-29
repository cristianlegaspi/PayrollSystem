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
            ->columns([
                TextColumn::make('employee.full_name')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payrollPeriod.description')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('days_worked')
                    ->numeric()
                    ->sortable()
                     ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('days_absent')
                    ->numeric()
                     ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('undertime_hours')
                    ->numeric()
                     ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('overtime_hours')
                    ->numeric()
                    ->sortable()
                     ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('night_diff_hours')
                    ->numeric()
                     ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('night_diff_ot_hours')
                    ->numeric()
                     ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('daily_rate')
                    ->numeric()
                     ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('basic_salary')
                    ->numeric()
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('overtime_salary')
                    ->numeric()
                    ->label('Regular Overtime Salary')
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('night_diff_salary')
                    ->numeric()
                    ->money('PHP')
                     ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('night_diff_ot_salary')
                    ->numeric()
                    ->money('PHP')
                     ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                  TextColumn::make('undertime_deduction')
                    ->numeric()
                    ->money('PHP')
                     ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('gross_pay')
                    ->numeric()
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('total_deductions')
                    ->numeric()
                    ->label('Contribution')
                    ->color('danger')
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('cash_advance')
                ->money('PHP')
                    ->numeric()
                     ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                      TextColumn::make('shortages')
                    ->numeric()
                     ->badge()
                    ->money('PHP')
                     ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                  TextColumn::make('other_deduction')
                    ->numeric()
                     ->badge()
                    ->money('PHP')
                     ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])
                ->visible(fn () => in_array(auth()->user()->role->role_name, ['Admin', 'Super Admin'])),
            ]);
    }
}
