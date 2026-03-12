<?php

namespace App\Filament\Resources\PayrollPeriods\Pages;

use App\Filament\Resources\PayrollPeriods\PayrollPeriodResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPayrollPeriod extends EditRecord
{
    protected static string $resource = PayrollPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            // DeleteAction::make(),
        ];
    }

     protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
     
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
        ->success()
        ->title('Payroll Period Updated')
        ->body('The Payroll Period has been updated successfully');
    }
}
