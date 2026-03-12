<?php

namespace App\Filament\Resources\EmploymentStatuses\Pages;

use App\Filament\Resources\EmploymentStatuses\EmploymentStatusResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateEmploymentStatus extends CreateRecord
{
    protected static string $resource = EmploymentStatusResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationBody(): ?string
    {
        return 'The Employment Status has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Employment Status Created')
            ->body($this->getCreatedNotificationBody());
    }
}
