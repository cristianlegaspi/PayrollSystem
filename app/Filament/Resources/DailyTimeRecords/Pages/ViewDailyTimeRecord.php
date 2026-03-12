<?php

namespace App\Filament\Resources\DailyTimeRecords\Pages;

use App\Filament\Resources\DailyTimeRecords\DailyTimeRecordResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDailyTimeRecord extends ViewRecord
{
    protected static string $resource = DailyTimeRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
