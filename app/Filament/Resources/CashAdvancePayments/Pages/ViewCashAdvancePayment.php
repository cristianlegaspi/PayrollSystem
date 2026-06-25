<?php

namespace App\Filament\Resources\CashAdvancePayments\Pages;

use App\Filament\Resources\CashAdvancePayments\CashAdvancePaymentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCashAdvancePayment extends ViewRecord
{
    protected static string $resource = CashAdvancePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
