<?php

namespace App\Filament\Resources\EmploymentStatuses\Pages;

use App\Filament\Resources\EmploymentStatuses\EmploymentStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditEmploymentStatus extends EditRecord
{
    protected static string $resource = EmploymentStatusResource::class;

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
        ->title('Employment Status Updated')
        ->body('The Employment Status has been updated successfully');
    }
}
