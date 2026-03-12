<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
    
    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationBody(): ?string
    {
        return 'The Role has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Role Created')
            ->body($this->getCreatedNotificationBody());
    }
}
