<?php

namespace App\Filament\Resources\LeaveBalances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeaveBalancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('employee_id')
            ->columns([

                TextColumn::make('employee.branch.branch_name')
                    ->label('Branch')
                    ->searchable(),

                TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('year')
                    ->sortable(),

                TextColumn::make('annual_credit')
                    ->label('Annual Credit'),

                TextColumn::make('used_credit')
                    ->label('Used'),

                TextColumn::make('remaining_credit')
                    ->label('Remaining')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
