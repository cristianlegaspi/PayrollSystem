<?php

namespace App\Filament\Resources\CashAdvancePayments\Pages;

use App\Filament\Resources\CashAdvancePayments\CashAdvancePaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCashAdvancePayments extends ListRecords
{
    protected static string $resource = CashAdvancePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
             ->label('Create New Payment'),
        ];
    }

    protected ?string $heading = 'Cash Advance Management';
    protected ?string $subheading = 'Overview of All Available Cash Advance Payments';

}
