<?php

namespace App\Filament\Resources\EmploymentTypes\Pages;

use App\Filament\Resources\EmploymentTypes\EmploymentTypesResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditEmploymentTypes extends EditRecord
{
    protected static string $resource = EmploymentTypesResource::class;

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
        ->title('Employment Types Updated')
        ->body('The Employment Types has been updated successfully');
    }
}
