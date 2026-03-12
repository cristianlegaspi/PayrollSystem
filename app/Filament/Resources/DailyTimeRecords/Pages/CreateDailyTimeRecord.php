<?php

namespace App\Filament\Resources\DailyTimeRecords\Pages;

use App\Filament\Resources\DailyTimeRecords\DailyTimeRecordResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;


class CreateDailyTimeRecord extends CreateRecord
{
    protected static string $resource = DailyTimeRecordResource::class;

     protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationBody(): ?string
    {
        return 'The DTR has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New DTR Created')
            ->body($this->getCreatedNotificationBody());
    }
}
