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
                ->form([
                    Select::make('employee_id')
                        ->label('Employee')
                        ->relationship(
                            name: 'employee',
                            titleAttribute: 'full_name',
                            modifyQueryUsing: function ($query, $get) use ($user) {
                                $roleName = $user->role?->role_name;

                                // Admin/Super Admin can see all employees
                                if (in_array($roleName, ['Admin', 'Super Admin', 'Owner'])) {
                                    return $get('branch_id')
                                        ? $query->where('branch_id', $get('branch_id'))
                                        : $query;
                                }
                                  if ($user->branch?->branch_name === 'All Branch') {
                                    return $query;
                                }

                                // Normal users: only employees in their branch
                                return $query->where('branch_id', $user->branch_id);
                            }
                        )
                        ->searchable()
                        ->preload()
                        ->required(),

                    DatePicker::make('from')->label('Work Date From'),
                    DatePicker::make('to')->label('Work Date To'),
                ])
                ->action(function ($data) {
                    $params = array_filter([
                        'employee_id' => $data['employee_id'] ?? null,
                        'from' => $data['from'] ?? null,
                        'to' => $data['to'] ?? null,
                    ]);

                    return redirect()->to(route('dtr.print', $params));
                })
                ->openUrlInNewTab(),

                 CreateAction::make()
                ->label('Create New DTR'),

        ];
    }
}