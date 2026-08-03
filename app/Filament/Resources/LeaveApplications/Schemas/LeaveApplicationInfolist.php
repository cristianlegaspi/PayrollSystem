<?php

namespace App\Filament\Resources\LeaveApplications\Schemas;


use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class LeaveApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Employee Information')
                    ->columns(2)
                    ->schema([

                        TextEntry::make('employee.full_name')
                            ->label('Employee'),

                        TextEntry::make('created_at')
                            ->label('Date Filed')
                            ->dateTime('F d, Y h:i A'),

                    ]) ->columnSpanFull(),

                Section::make('Leave Details')
                    ->columns(2)
                    ->schema([

                        TextEntry::make('leave_type')
                            ->label('Leave Type')
                            ->badge(),

                        TextEntry::make('days')
                            ->label('Number of Leave Days')
                            ->numeric(),

                        TextEntry::make('from_date')
                            ->label('From Date')
                            ->date('F d, Y'),

                        TextEntry::make('to_date')
                            ->label('To Date')
                            ->date('F d, Y'),

                        TextEntry::make('reason')
                            ->label('Reason')
                            ->placeholder('-'),

                    ]) ->columnSpanFull(),

                Section::make('Approval Information')
                    ->columns(2)
                    ->schema([

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Pending' => 'warning',
                                'Approved' => 'success',
                                'Rejected' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('approved_date')
                            ->label('Approved Date')
                            ->dateTime('F d, Y h:i A')
                            ->placeholder('Not yet approved'),

                    ]) ->columnSpanFull(),

            ]);
    }
}