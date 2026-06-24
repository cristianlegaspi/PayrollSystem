<?php

namespace App\Filament\Resources\PayrollAdjustments\Pages;

use App\Filament\Resources\PayrollAdjustments\PayrollAdjustmentResource;
use App\Filament\Widgets\PayrollAdjustmentStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayrollAdjustments extends ListRecords
{
    protected static string $resource = PayrollAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
             ->label('Create New Payroll Adjustment'),
        ];
    }

    protected ?string $heading = 'Payroll Adjustment Management';
    protected ?string $subheading = 'Overview of All Available Payroll Adjustments';

      protected function getHeaderWidgets(): array
    {
        return [
            PayrollAdjustmentStatsOverview::class,
        ];
    }
}