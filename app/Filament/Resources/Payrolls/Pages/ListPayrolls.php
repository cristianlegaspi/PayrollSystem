<?php

namespace App\Filament\Resources\Payrolls\Pages;

use App\Filament\Resources\Payrolls\PayrollResource;
use App\Services\PayrollService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Models\PayrollPeriod;
use App\Models\Payroll;
use App\Models\Branch;
use Barryvdh\DomPDF\Facade\Pdf;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [

           // ================= PRINT PAYROLL REPORT =================
Action::make('printPayrollReport')
    ->label('Print Payroll Report')
    ->icon('heroicon-o-printer')
    ->color('success')
    ->form([
        Select::make('payroll_period_id')
            ->label('Finalized Payroll Period')
            ->relationship(
                name: 'payrollPeriod',
                titleAttribute: 'description',
                modifyQueryUsing: fn ($query) => $query->where('status', 'finalized')
            )
            ->searchable()
            ->preload()
            ->required(),

        Select::make('branch_id')
            ->label('Branch')
            ->options(Branch::pluck('branch_name', 'id'))
            ->searchable()
            ->preload()
            ->required(),
    ])
    ->action(function (array $data) {
        // We generate a signed URL or a specific route to handle the PDF generation
        // However, for a quick and direct approach in Filament, 
        // we can use a redirect to a dedicated controller route.
        
        return redirect()->route('payroll.print', [
            'period' => $data['payroll_period_id'],
            'branch' => $data['branch_id'],
        ]);
    })
    ->openUrlInNewTab(), // This is the key method

            // ================= GENERATE PAYROLL =================
            Action::make('generatePayroll')
                ->label('Generate Payroll')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary')

                ->form([
                    Select::make('payroll_period_id')
                        ->label('Payroll Period')
                        ->relationship(
                            name: 'payrollPeriod',
                            titleAttribute: 'description',
                            modifyQueryUsing: fn ($query) => $query->where('status', 'open')
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                ])

                ->requiresConfirmation()

                ->action(function (array $data) {

                    try {

                        $period = PayrollPeriod::findOrFail($data['payroll_period_id']);

                        $service = new PayrollService();
                        $service->computePayrollForPeriod($period);

                        Notification::make()
                            ->title('Payroll generated successfully!')
                            ->success()
                            ->send();

                    } catch (\Throwable $e) {

                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

        ];
        
    }

     protected ?string $heading = 'Payroll Management';
    protected ?string $subheading = 'Overview of All Payroll';
}