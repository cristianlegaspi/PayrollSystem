<?php

namespace App\Filament\Resources\CashAdvances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Branch;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use App\Models\Employee;

class CashAdvancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ca_no')
                    ->label('C.A No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'previous_balance' => 'Previous Balance',
                        'cash_advance' => 'Cash Advance',
                        'motor_assistance' => 'Motor Assistance',
                        'adjustment_add' => 'Adjustment Add',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'previous_balance' => 'gray',
                        'cash_advance' => 'danger',
                        'motor_assistance' => 'warning',
                        'adjustment_add' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Paid / Deducted')
                    ->getStateUsing(fn ($record) => $record->paid_amount)
                    ->money('PHP')
                    ->color('success'),

                TextColumn::make('balance')
                    ->label('Balance')
                    ->getStateUsing(fn ($record) => $record->balance)
                    ->money('PHP')
                    ->color(fn ($state): string => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->status)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'partial' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(40)
                    ->searchable()
                      ->toggleable(isToggledHiddenByDefault: true),
            ])
          ->filters([
                Filter::make('branch_employee')
                    ->label('Branch / Employee')
                    ->form([
                        Select::make('branch_id')
                            ->label('Branch')
                            ->options(
                                fn() => Branch::query()
                                    ->orderBy('branch_name')
                                    ->pluck('branch_name', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn($set) => $set('employee_id', null)),

                        Select::make('employee_id')
                            ->label('Employee')
                            ->options(function ($get) {
                                $branchId = $get('branch_id');

                                return Employee::query()
                                    ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
                                    ->orderBy('full_name')
                                    ->pluck('full_name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['branch_id'] ?? null,
                                fn(Builder $query, $branchId): Builder => $query->whereHas(
                                    'employee',
                                    fn(Builder $employeeQuery): Builder => $employeeQuery->where('branch_id', $branchId)
                                )
                            )
                            ->when(
                                $data['employee_id'] ?? null,
                                fn(Builder $query, $employeeId): Builder => $query->where('employee_id', $employeeId)
                            );
                    }),

                SelectFilter::make('payment_type')
                    ->label('Payment Type')
                    ->options([
                        'payment' => 'Payment',
                    ]),
            ])
            ->defaultSort('transaction_date', 'desc')
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