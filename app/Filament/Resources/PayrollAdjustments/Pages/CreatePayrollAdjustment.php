<?php

namespace App\Filament\Resources\PayrollAdjustments\Pages;

use App\Filament\Resources\PayrollAdjustments\PayrollAdjustmentResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePayrollAdjustment extends CreateRecord
{
    protected static string $resource = PayrollAdjustmentResource::class;

    protected function getRedirectUrl(): string
    
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationBody(): ?string
    {
        return 'The Payroll Adjustment has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Payroll Adjustment Created')
            ->body($this->getCreatedNotificationBody());
    }


}