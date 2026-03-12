<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationBody(): ?string
    {
        return 'The Branch has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Branch Created')
            ->body($this->getCreatedNotificationBody());
    }
}
