<?php

namespace App\Filament\Resources\LeaveBalances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveBalancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Show only Active employees
            ->modifyQueryUsing(fn (Builder $query) => $query->whereHas(
                'employee',
                fn (Builder $query) => $query->where('status', 'Active')
            ))

            ->defaultSort('employee_id')

            ->columns([

                TextColumn::make('employee.branch.branch_name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'warning',
                        'Resigned' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('year')
                    ->sortable(),

                TextColumn::make('annual_credit')
                    ->label('Annual Credit')
                    ->alignCenter(),

                TextColumn::make('used_credit')
                    ->label('Used')
                    ->alignCenter(),

                TextColumn::make('remaining_credit')
                    ->label('Remaining')
                    ->badge()
                    ->alignCenter()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),

            ])

            ->filters([

            ])

            ->recordActions([

                //

            ])

            ->toolbarActions([

                BulkActionGroup::make([

                    DeleteBulkAction::make(),

                ]),

            ]);
    }
}