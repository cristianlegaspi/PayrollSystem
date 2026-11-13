<?php

namespace App\Filament\Resources\Payrolls\Tables;

use App\Models\PayrollPeriod;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

                // ✅ FILTER: Payroll Period (pinakabago ang nasa taas)
                SelectFilter::make('payroll_period_id')
                    ->label('Payroll Period')
                    ->relationship(
                        name: 'payrollPeriod',
                        titleAttribute: 'description',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->orderByDesc('start_date')
                            ->orderByDesc('id'),
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),

                // ✅ FILTER: Remarks (galing sa payroll period)
                SelectFilter::make('remarks')
                    ->label('Remarks')
                    ->options([
                        'Approved' => 'Approved',
                        'Pending'  => 'Pending',
                        'Rejected' => 'Rejected',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas(
                                'payrollPeriod',
                                fn (Builder $q) => $q->where('remarks', $data['value'])
                            );
                        }
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
                //
            ]);
    }
}