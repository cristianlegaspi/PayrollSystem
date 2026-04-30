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

            ->modifyQueryUsing(function ($query) {
                // Default: show Pending only
                if (! request()->has('tableFilters')) {
                    $query->whereHas('payrollPeriod', function ($q) {
                        $q->where('status', 'Finalized')
                          ->where('remarks', 'Pending');
                    });
                }
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
                SelectFilter::make('payroll_period_id')
                    ->label('Approved Payroll Period')
                    ->options(function () {
                        return PayrollPeriod::query()
                            ->where('status', 'Finalized')
                            ->where('remarks', 'Approved')
                            ->pluck('description', 'id')
                            ->toArray();
                    })
                    ->query(function ($query, array $data) {

                        if (! filled($data['value'])) {
                            // If no filter selected → revert to Pending only
                            return $query->whereHas('payrollPeriod', function ($q) {
                                $q->where('status', 'Finalized')
                                  ->where('remarks', 'Pending');
                            });
                        }

                        // If selected → show Approved
                        return $query->where('payroll_period_id', $data['value']);
                    }),
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
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])
                ->visible(fn () => in_array(auth()->user()->role->role_name, ['Admin', 'Super Admin'])),
            ]);
    }
}