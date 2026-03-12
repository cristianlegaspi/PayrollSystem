<?php

namespace App\Filament\Resources\DailyTimeRecords\Pages;

use App\Filament\Resources\DailyTimeRecords\DailyTimeRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditDailyTimeRecord extends EditRecord
{
    protected static string $resource = DailyTimeRecordResource::class;

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
        ->title('DTR Record Updated')
        ->body('The DTR record has been updated successfully');
    }
}
