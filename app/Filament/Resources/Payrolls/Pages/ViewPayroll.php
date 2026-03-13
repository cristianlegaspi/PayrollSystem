<?php

namespace App\Filament\Resources\Payrolls\Pages;

use App\Filament\Resources\Payrolls\PayrollResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use App\Services\PayslipService;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewPayroll extends ViewRecord
{
    protected static string $resource = PayrollResource::class;

   protected function getHeaderActions(): array
{
    return [
        Action::make('downloadPayslip')
            ->label('Download Payslip PDF')
            ->icon('heroicon-o-arrow-down-tray') // Added a nice icon for you
            ->color('primary')
            // Change 'payslip.generate' to 'payroll.payslip'
            ->url(fn ($record) => route('payroll.payslip', $record)) 
            ->openUrlInNewTab(),
    ];
}
}