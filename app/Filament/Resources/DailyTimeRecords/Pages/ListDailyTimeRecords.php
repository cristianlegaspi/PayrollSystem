<?php

namespace App\Filament\Resources\DailyTimeRecords\Pages;

use App\Filament\Resources\DailyTimeRecords\DailyTimeRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use App\Models\Employee;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;

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

            Action::make('Import Excel')
                ->label('Import Excel')
                ->color('warning')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->label('DTR Excel File')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->disk('local')
                        ->directory('temp-imports')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $path = storage_path('app/private/' . $data['file']);

                    if (!file_exists($path)) {
                        $path = storage_path('app/' . $data['file']);
                    }

                    \Maatwebsite\Excel\Facades\Excel::import(
                        new \App\Imports\DTRImport(auth()->user()),
                        $path
                    );

                    Notification::make()
                        ->title('DTR import completed successfully.')
                        ->success()
                        ->send();
                }),





            // ✅ CREATE BUTTON
            CreateAction::make()
                ->label('Create New DTR'),

        ];
    }
}
