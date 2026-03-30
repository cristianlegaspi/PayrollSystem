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
        // Capture the logged-in user outside closures to avoid Filament context issues
        $user = auth()->user();

        return [
            // Button to create new DTR

            // Export PDF button with Employee & Date filters
            Action::make('Export PDF')
                ->label('Generate DTR report')
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
                                // If not Admin/Owner, restrict to their own branch
                                if (!in_array($roleName, ['Admin', 'Super Admin', 'Owner'])) {
                                    return $query->where('branch_id', $user->branch_id);
                                }
                                return $query;
                            }
                        )
                        ->searchable()
                        ->preload(), // Removed 'required()' to allow bulk generation

                    DatePicker::make('from')
                        ->label('Work Date From')
                        ->required(), // Dates are usually required for a clean report
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

            CreateAction::make()
                ->label('Create New DTR'),

        ];
    }
}
