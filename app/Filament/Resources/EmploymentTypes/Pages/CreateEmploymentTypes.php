<?php

namespace App\Filament\Resources\EmploymentTypes\Pages;

use App\Filament\Resources\EmploymentTypes\EmploymentTypesResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateEmploymentTypes extends CreateRecord
{
    protected static string $resource = EmploymentTypesResource::class;

     protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationBody(): ?string
    {
        return 'The Employment Types has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Employment Types Created')
            ->body($this->getCreatedNotificationBody());
    }
}
