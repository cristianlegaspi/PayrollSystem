<?php

namespace App\Filament\Resources\Contributions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContributionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.full_name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sss_ee')
                    ->numeric()
                    ->label('SSS EE')
                    ->sortable(),
                TextColumn::make('sss_er')
                    ->numeric()
                    ->label('SSS ER')
                    ->sortable(),
                TextColumn::make('premium_voluntary_ss_contribution')
                    ->numeric()
                    ->label('Premium SS Contribution')
                    ->sortable(),
                TextColumn::make('sss_salary_loan')
                    ->numeric()
                    ->label('SSS Salary Loan')
                    ->sortable(),
                TextColumn::make('sss_calamity_loan')
                    ->numeric()
                    ->label('SSS Calamity Loan')
                    ->sortable(),
                TextColumn::make('philhealth_ee')
                    ->numeric()
                    ->label('Philhealth EE')
                    ->sortable(),
                TextColumn::make('philhealth_er')
                    ->numeric()
                    ->label('Philhealth ER')
                    ->sortable(),
                TextColumn::make('pagibig_ee')
                    ->numeric()
                    ->label('PAGIBIG EE')
                    ->sortable(),
                TextColumn::make('pagibig_er')
                    ->numeric()
                    ->label('PAGIBIG ER')
                    ->sortable(),
                TextColumn::make('pagibig_salary_loan')
                    ->numeric()
                    ->label('PAGIBIG Salary Loan')
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
                    ->visible(fn() => in_array(auth()->user()->role->role_name, ['Admin', 'Super Admin'])),
            ]);
    }
}
