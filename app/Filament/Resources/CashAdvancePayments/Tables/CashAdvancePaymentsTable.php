<?php

namespace App\Filament\Resources\CashAdvancePayments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Branch;


class CashAdvancePaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_no')
                    ->label('Payment No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cashAdvance.ca_no')
                    ->label('C.A No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'payment' => 'Payment',
                        'deduction' => 'Deduction',
                        'adjustment_less' => 'Adjustment Less',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'payment' => 'success',
                        'deduction' => 'info',
                        'adjustment_less' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('cashAdvance.balance')
                    ->label('Remaining C.A Balance')
                    ->getStateUsing(fn ($record) => $record->cashAdvance?->balance ?? 0)
                    ->money('PHP')
                    ->color(fn ($state): string => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(40)
                    ->searchable()
                      ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'full_name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(fn () => Branch::query()
                        ->orderBy('branch_name')
                        ->pluck('branch_name', 'id')
                        ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn (Builder $query, $branchId): Builder => $query->whereHas(
                                'employee',
                                fn (Builder $employeeQuery): Builder => $employeeQuery->where('branch_id', $branchId)
                            )
                        );
                    }),

                SelectFilter::make('payment_type')
                    ->label('Payment Type')
                    ->options([
                        'payment' => 'Payment',
                    ]),
            ])
            ->defaultSort('payment_date', 'desc')
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