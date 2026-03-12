<?php

namespace App\Filament\Resources\Positions\Pages;

use App\Filament\Resources\Positions\PositionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePosition extends CreateRecord
{
    protected static string $resource = PositionResource::class;

      protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationBody(): ?string
    {
        return 'The Position has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Position Created')
            ->body($this->getCreatedNotificationBody());
    }
}
