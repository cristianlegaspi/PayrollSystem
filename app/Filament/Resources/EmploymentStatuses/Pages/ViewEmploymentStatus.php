<?php

namespace App\Filament\Resources\EmploymentStatuses\Pages;

use App\Filament\Resources\EmploymentStatuses\EmploymentStatusResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEmploymentStatus extends ViewRecord
{
    protected static string $resource = EmploymentStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
