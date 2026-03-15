<?php

namespace App\Filament\Resources\DailyTimeRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use App\Models\Branch;

class DailyTimeRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.full_name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('work_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('shift1_time_in')
                      ->time('h:i A') // Changed this
                    ->sortable(),
                TextColumn::make('shift1_time_out')
                       ->time('h:i A') // Changed this
                    ->sortable(),
                TextColumn::make('shift2_time_in')
                       ->time('h:i A') // Changed this
                    ->sortable(),
                TextColumn::make('shift2_time_out')
                       ->time('h:i A') // Changed this
                    ->sortable(),
                TextColumn::make('shift3_time_in')
                      ->time('h:i A') // Changed this
                    ->sortable(),
                TextColumn::make('shift3_time_out')
                      ->time('h:i A') // Changed this
                    ->sortable(),
                TextColumn::make('overtime_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('undertime_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sunday_ot_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('remarks')
                    ->searchable(),
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

                Filter::make('branch')
                        ->form([
                            // Load all branches for selection
                            \Filament\Forms\Components\Select::make('branch_id')
                                ->label('Branch')
                                ->options(Branch::pluck('branch_name', 'id')->toArray())
                                ->searchable(),
                        ])
                        ->query(function (Builder $query, array $data) {
                            if (!empty($data['branch_id'])) {
                                $query->whereHas('employee.branch', function ($q) use ($data) {
                                    $q->where('id', $data['branch_id']);
                                });
                            }
                        }),
                 Filter::make('work_date_range')
                    ->form([
                        DatePicker::make('start_date')->label('Start Date'),
                        DatePicker::make('end_date')->label('End Date'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['start_date'] ?? false) {
                            $query->whereDate('work_date', '>=', $data['start_date']);
                        }
                        if ($data['end_date'] ?? false) {
                            $query->whereDate('work_date', '<=', $data['end_date']);
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
