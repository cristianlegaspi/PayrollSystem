<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_number')
                    ->label('Emp No.')
                    ->searchable(),
                TextColumn::make('full_name')
                    ->searchable(),
                TextColumn::make('position.position_name')
                    ->numeric()
                    ->label('Position')
                    ->sortable(),
                TextColumn::make('branch.branch_name')
                    ->numeric()
                    ->label('Branch')
                    ->sortable(),
                TextColumn::make('employmentStatus.name')
                    ->numeric()
                    ->label('Employment Status')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('employmentType.name')
                    ->numeric()
                    ->label('Employment Types')
                    ->sortable(),
                TextColumn::make('daily_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('date_hired')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('date_of_birth')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('tin')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
               TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'Active',
                        'warning' => 'Resigned',
                        'danger' => 'Terminated',
                    ]),
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
                SelectFilter::make('position_id')
                    ->label('Position')
                    ->relationship('position', 'position_name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'branch_name')
                    ->searchable()
                    ->preload(),

                 SelectFilter::make('employment_status_id')
                    ->label('Employment Status')
                    ->relationship('employmentStatus', 'name')
                    ->searchable()
                    ->preload(),

            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])
                    ->visible(fn() => in_array(auth()->user()->role->role_name, ['Admin', 'Super Admin'])),
            ]);
    }
}
