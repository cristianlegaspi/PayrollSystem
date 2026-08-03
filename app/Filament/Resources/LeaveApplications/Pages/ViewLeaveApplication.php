<?php

namespace App\Filament\Resources\LeaveApplications\Pages;

use App\Filament\Resources\LeaveApplications\LeaveApplicationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLeaveApplication extends ViewRecord
{
    protected static string $resource = LeaveApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
