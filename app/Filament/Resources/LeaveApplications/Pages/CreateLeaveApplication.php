<?php

namespace App\Filament\Resources\LeaveApplications\Pages;

use App\Filament\Resources\LeaveApplications\LeaveApplicationResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateLeaveApplication extends CreateRecord
{
    protected static string $resource = LeaveApplicationResource::class;

     protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationBody(): ?string
    {
        return 'The Leave Application has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Leave Application Created')
            ->body($this->getCreatedNotificationBody());
    }
}
