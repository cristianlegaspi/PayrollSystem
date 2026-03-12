<?php

namespace App\Filament\Resources\EmploymentStatuses\Pages;

use App\Filament\Resources\EmploymentStatuses\EmploymentStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmploymentStatuses extends ListRecords
{
    protected static string $resource = EmploymentStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
