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
    //->icon('heroicon-o-download')
    ->color('primary')
    ->action(function ($record, PayslipService $service) {
        $data = $service->generate($record);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payslip.generate', compact('data'));
        return response()->streamDownload(
            fn() => print($pdf->output()),
            "Payslip-{$record->employee->full_name}.pdf"
        );
    }),
        ];
    }
}