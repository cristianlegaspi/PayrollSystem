<?php

namespace App\Filament\Resources\CashAdvancePayments\Pages;

use App\Filament\Resources\CashAdvancePayments\CashAdvancePaymentResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateCashAdvancePayment extends CreateRecord
{
    protected static string $resource = CashAdvancePaymentResource::class;

     protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationBody(): ?string
    {
        return 'The Cash Advance Payment has been created successfully.';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New Cash Advance Payment Created')
            ->body($this->getCreatedNotificationBody());
    }
}
