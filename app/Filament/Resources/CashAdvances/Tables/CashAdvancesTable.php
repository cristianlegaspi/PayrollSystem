<?php

namespace App\Filament\Resources\CashAdvances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'full_name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'cash_advance' => 'Cash Advance (CA)',
                        'motor_assistance' => 'Motor Assistance',
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