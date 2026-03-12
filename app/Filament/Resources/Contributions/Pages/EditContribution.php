<?php

namespace App\Filament\Resources\Contributions\Pages;

use App\Filament\Resources\Contributions\ContributionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditContribution extends EditRecord
{
    protected static string $resource = ContributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            // DeleteAction::make(),
        ];
    }

      protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
     
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
        ->success()
        ->title('Contribution Updated')
        ->body('The Contribution has been updated successfully');
    }
}
