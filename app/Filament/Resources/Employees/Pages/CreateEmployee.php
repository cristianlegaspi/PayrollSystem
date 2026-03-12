<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationBody(): ?string
    {
        return 'The Employee has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Employee Created')
            ->body($this->getCreatedNotificationBody());
    }
}
