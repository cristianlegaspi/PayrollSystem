<?php

namespace App\Filament\Resources\PayrollPeriods\Pages;

use App\Filament\Resources\PayrollPeriods\PayrollPeriodResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePayrollPeriod extends CreateRecord
{
    protected static string $resource = PayrollPeriodResource::class;

     protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationBody(): ?string
    {
        return 'The Payroll Period has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Payroll Period Created')
            ->body($this->getCreatedNotificationBody());
    }
}
