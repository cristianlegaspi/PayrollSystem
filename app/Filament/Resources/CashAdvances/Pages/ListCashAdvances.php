<?php

namespace App\Filament\Resources\CashAdvances\Pages;

use App\Filament\Resources\CashAdvances\CashAdvanceResource;
use App\Models\Branch;
use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Js;


class ListCashAdvances extends ListRecords
{
    protected static string $resource = CashAdvanceResource::class;

    protected ?string $heading = 'Cash Advance Management';

    protected ?string $subheading = 'Overview of All Available Cash Advances';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_cash_advance')
                ->label('Print Cash Advance')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->form([
                    Select::make('scope')
                        ->label('Print Option')
                        ->options([
                            'employee' => 'Per Employee',
                            'branch' => 'Per Branch',
                            'all' => 'All Employees',
                        ])
                        ->default('employee')
                        ->live()
                        ->required()
                        ->afterStateUpdated(function ($set) {
                            $set('branch_id', null);
                            $set('employee_id', null);
                        }),

                    Select::make('branch_id')
                        ->label('Branch')
                        ->options(
                            fn() => Branch::query()
                                ->orderBy('branch_name')
                                ->pluck('branch_name', 'id')
                                ->toArray()
                        )
                        ->searchable()
                        ->preload()
                        ->live()
                        ->visible(fn($get) => in_array($get('scope'), ['employee', 'branch'], true))
                        ->required(fn($get) => in_array($get('scope'), ['employee', 'branch'], true))
                        ->afterStateUpdated(function ($set) {
                            $set('employee_id', null);
                        }),

                    Select::make('employee_id')
                        ->label('Employee')
                        ->options(function ($get) {
                            $branchId = $get('branch_id');

                            if (! $branchId) {
                                return [];
                            }

                            return Employee::query()
                                ->where('branch_id', $branchId)
                                ->whereHas('cashAdvances')
                                ->orderBy('full_name')
                                ->get()
                                ->mapWithKeys(fn($employee) => [
                                    $employee->id => $employee->display_name
                                        . ' - Balance: ₱'
                                        . number_format($employee->cash_advance_balance, 2),
                                ])
                                ->toArray();
                        })
                        ->searchable()
                        ->preload()
                        ->visible(fn($get) => $get('scope') === 'employee')
                        ->required(fn($get) => $get('scope') === 'employee')
                        ->disabled(fn($get) => blank($get('branch_id')))
                        ->helperText('Only employees with Cash Advance records will appear.'),
                ])
                ->modalHeading('Print Cash Advance Statement')
                ->modalSubmitActionLabel('Print')
                ->action(function (array $data): void {
                    $url = route('cash-advances.print', [
                        'scope' => $data['scope'],
                        'branch_id' => $data['branch_id'] ?? null,
                        'employee_id' => $data['employee_id'] ?? null,
                    ]);

                    $this->js('window.open(' . Js::from($url) . ', "_blank");');
                }),

            CreateAction::make()
                ->label('Create New Cash Advance'),
        ];
    }



  
}
