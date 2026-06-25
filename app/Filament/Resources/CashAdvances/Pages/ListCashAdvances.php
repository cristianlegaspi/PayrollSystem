<?php

namespace App\Filament\Resources\CashAdvances\Pages;

use App\Filament\Resources\CashAdvances\CashAdvanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use App\Models\Employee;
use Filament\Forms\Components\Select;
use Illuminate\Support\Js;

class ListCashAdvances extends ListRecords
{
    protected static string $resource = CashAdvanceResource::class;

    protected function getHeaderActions(): array
    {
        return [

          Action::make('print_by_employee')
                ->label('Print Cash Advance')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->form([
                    Select::make('employee_id')
                        ->label('Employee')
                        ->options(
                            Employee::query()
                                ->orderBy('full_name')
                                ->get()
                                ->mapWithKeys(fn ($employee) => [
                                    $employee->id => $employee->display_name,
                                ])
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->modalHeading('Print Cash Advance Statement')
                ->modalSubmitActionLabel('Print')
                ->action(function (array $data): void {
                    $url = route('cash-advances.print', $data['employee_id']);

                    $this->js('window.open(' . Js::from($url) . ', "_blank");');
                }),

            CreateAction::make()
            ->label('Create New Cash Advance'),
        ];
    }

    protected ?string $heading = 'Cash Advance Management';
    protected ?string $subheading = 'Overview of All Available Cash Advances';
}
