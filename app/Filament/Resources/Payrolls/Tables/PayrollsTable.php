<?php

namespace App\Filament\Resources\Payrolls\Tables;

use App\Models\PayrollPeriod;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PayrollsTable
{
    public static function configure(Table $table): Table
    {
        return $table

         ->defaultSort(function ($query) {
                return $query
                    ->orderByDesc(
                        PayrollPeriod::query()
                            ->select('start_date')
                            ->whereColumn(
                                'payroll_periods.id',
                                'payrolls.payroll_period_id'
                            )
                            ->limit(1)
                    )
                    ->orderByDesc('payrolls.id');
            })



            ->columns([
                TextColumn::make('employee.full_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payrollPeriod.description')
                    ->label('Payroll Period')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payrollPeriod.remarks')
                    ->label('Remarks')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Approved' => 'success',
                        'Pending' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('gross_pay')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('net_pay')
                    ->money('PHP')
                    ->badge()
                    ->color('success')
                    ->sortable(),
            ])

            ->filters([
              
            ])

            ->recordActions([
                ViewAction::make(),
            EditAction::make()
            ->visible(fn ($record) =>
                $record->payrollPeriod?->status === 'Finalized'
                && $record->payrollPeriod?->remarks === 'Pending'
            ),
            ])

            ->toolbarActions([
                
            ]);
    }
}