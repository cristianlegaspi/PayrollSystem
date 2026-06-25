<?php

namespace App\Filament\Resources\CashAdvancePayments\Pages;

use App\Filament\Resources\CashAdvancePayments\CashAdvancePaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCashAdvancePayment extends EditRecord
{
    protected static string $resource = CashAdvancePaymentResource::class;

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
        ->title('Cash Advance Payment Updated')
        ->body('The Cash Advance Payment has been updated successfully');
    }
}
