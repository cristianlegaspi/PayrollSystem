<?php

namespace App\Filament\Resources\DailyTimeRecords\Pages;

use App\Filament\Resources\DailyTimeRecords\DailyTimeRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use App\Models\Employee;

class ListDailyTimeRecords extends ListRecords
{
    protected static string $resource = DailyTimeRecordResource::class;

    protected ?string $heading = 'Daily Time Record (DTR) Management';
    protected ?string $subheading = 'Overview of All DTR';

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        return [

            // ✅ EXPORT PDF
            Action::make('Export PDF')
                ->label('Generate DTR PDF')
                ->color('success')
                ->icon('heroicon-o-printer')
                ->form([
                    Select::make('employee_id')
                        ->label('Employee (Leave blank for ALL)')
                        ->placeholder('All Employees')
                        ->relationship(
                            name: 'employee',
                            titleAttribute: 'full_name',
                            modifyQueryUsing: function ($query) use ($user) {
                                $roleName = $user->role?->role_name;

                                if (!in_array($roleName, ['Admin', 'Super Admin', 'Owner'])) {
                                    return $query->where('branch_id', $user->branch_id);
                                }

                                return $query;
                            }
                        )
                        ->searchable()
                        ->preload(),

                    DatePicker::make('from')
                        ->label('Work Date From')
                        ->required(),

                    DatePicker::make('to')
                        ->label('Work Date To')
                        ->required(),
                ])
                ->action(function ($data) {
                    return redirect()->to(route('dtr.print', [
                        'employee_id' => $data['employee_id'] ?? null,
                        'from' => $data['from'],
                        'to' => $data['to'],
                    ]));
                })
                ->openUrlInNewTab(),

            // ✅ EXPORT EXCEL
            Action::make('Export Excel')
                ->label('Export to Excel')
                ->color('primary')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    Select::make('employee_id')
                        ->label('Employee (Leave blank for ALL)')
                        ->placeholder('All Employees')
                        ->relationship(
                            name: 'employee',
                            titleAttribute: 'full_name',
                            modifyQueryUsing: function ($query) use ($user) {
                                $roleName = $user->role?->role_name;

                                if (!in_array($roleName, ['Admin', 'Super Admin', 'Owner'])) {
                                    return $query->where('branch_id', $user->branch_id);
                                }

                                return $query;
                            }
                        )
                        ->searchable()
                        ->preload(),

                    DatePicker::make('from')
                        ->label('Work Date From')
                        ->required(),

                    DatePicker::make('to')
                        ->label('Work Date To')
                        ->required(),
                ])
                ->action(function ($data) {
                    return redirect()->to(route('dtr.export.excel', [
                        'employee_id' => $data['employee_id'] ?? null,
                        'from' => $data['from'],
                        'to' => $data['to'],
                    ]));
                })
                ->openUrlInNewTab(),

            // ✅ CREATE BUTTON
            CreateAction::make()
                ->label('Create New DTR'),

        ];
    }
}