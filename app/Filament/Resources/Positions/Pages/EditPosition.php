<?php

namespace App\Filament\Resources\Positions\Pages;

use App\Filament\Resources\Positions\PositionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPosition extends EditRecord
{
    protected static string $resource = PositionResource::class;

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
        ->title('Position Updated')
        ->body('The Position has been updated successfully');
    }
}
