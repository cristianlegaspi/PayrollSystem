<?php

namespace App\Filament\Resources\Contributions\Pages;

use App\Filament\Resources\Contributions\ContributionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContribution extends ViewRecord
{
    protected static string $resource = ContributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
