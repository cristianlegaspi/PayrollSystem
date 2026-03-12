<?php

namespace App\Filament\Resources\Contributions\Pages;

use App\Filament\Resources\Contributions\ContributionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateContribution extends CreateRecord
{
    protected static string $resource = ContributionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationBody(): ?string
    {
        return 'The Contribution has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Contribution Created')
            ->body($this->getCreatedNotificationBody());
    }
}
