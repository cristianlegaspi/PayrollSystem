<?php

namespace App\Filament\Resources\Payrolls\Pages;

use App\Filament\Resources\Payrolls\PayrollResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPayroll extends EditRecord
{
    protected static string $resource = PayrollResource::class;

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
        ->title('Payroll Record Updated')
        ->body('The payroll record has been updated successfully');
    }
}
