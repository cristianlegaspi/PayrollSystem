<?php

namespace App\Filament\Resources\LeaveApplications\Pages;

use App\Filament\Resources\LeaveApplications\LeaveApplicationResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeaveApplications extends ListRecords
{
    protected static string $resource = LeaveApplicationResource::class;

    protected ?string $heading = 'Leave Management';

    protected ?string $subheading = 'Overview of All Leave Applications';

    protected function getHeaderActions(): array
    {
        return [

            CreateAction::make()
                ->label('Create New Leave Application'),

            Action::make('printSummary')
                ->label('Print Summary')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(route('leave.summary'))
                ->openUrlInNewTab(),

        ];
    }
}