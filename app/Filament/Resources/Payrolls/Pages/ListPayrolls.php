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

                    if (empty($data['payroll_period_id']) || empty($data['branch_id'])) {
                        Notification::make()
                            ->title('Please select payroll period and branch.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $period = PayrollPeriod::findOrFail($data['payroll_period_id']);
                    $branch = Branch::findOrFail($data['branch_id']);

                    // ✅ FILTER PAYROLL BY BRANCH
                    $payrolls = Payroll::with(['employee', 'employee.branch', 'contribution'])
                        ->where('payroll_period_id', $period->id)
                        ->whereHas('employee', function ($q) use ($data) {
                            $q->where('branch_id', $data['branch_id']);
                        })
                        ->orderBy('employee_id')
                        ->get();

                    if ($payrolls->isEmpty()) {
                        Notification::make()
                            ->title('No payrolls found for this branch.')
                            ->warning()
                            ->send();
                        return;
                    }

                    // ✅ GENERATE PDF
                    $pdf = Pdf::loadView('reports.payroll-summary', [
                        'period' => $period,
                        'payrolls' => $payrolls,
                        'branch' => $branch
                    ])->setPaper('legal', 'landscape');

                    return response()->stream(
                        fn () => print($pdf->output()),
                        200,
                        [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'inline; filename="Payroll-' . $branch->branch_name . '-' . $period->description . '.pdf"',
                        ]
                    );
                }),

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
}