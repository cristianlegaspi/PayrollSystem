<?php

namespace App\Filament\Resources\PayrollAdjustments\Pages;

use App\Filament\Resources\PayrollAdjustments\PayrollAdjustmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPayrollAdjustment extends ViewRecord
{
    protected static string $resource = PayrollAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
