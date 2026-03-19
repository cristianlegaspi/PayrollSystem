<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Branch;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Select;
use Barryvdh\DomPDF\Facade\Pdf; // Make sure barryvdh/laravel-dompdf is installed
use App\Models\Employee;
use Filament\Actions\Action;


class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        return [
            

        Action::make('Generate Employee Report')
            ->label('Generate Employee Report')
            ->color('success')
            ->form([
        Select::make('branch_id')
            ->label('Select Branch')
            ->options(fn() => ['all' => 'All Branch'] + Branch::pluck('branch_name', 'id')->toArray())
            ->required(),
           ])
         ->action(function ($data) {
        // Redirect to PDF route — NO binary return here
        return redirect()->to(route('employees.dtr.pdf', [
            'branch_id' => $data['branch_id'],
                 ]));
             })
            ->openUrlInNewTab() // Open PDF in new tab
            ->requiresConfirmation(),

        CreateAction::make()
        ->label('Create New Employee'),

        ];
    }
     protected ?string $heading = 'Employee Management';
    protected ?string $subheading = 'Overview of All Employees';
}
