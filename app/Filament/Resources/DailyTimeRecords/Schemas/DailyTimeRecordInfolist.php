<?php

namespace App\Filament\Resources\DailyTimeRecords\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class DailyTimeRecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([


                Section::make('Employee and Work Time Details')
                    ->schema([
                        TextEntry::make('employee.full_name')
                            ->label('Employee Name'),
                        TextEntry::make('work_date')
                            ->label('Work Date')
                            ->date(),


                    ])->columns(2),

                Section::make('Biometrics Details')
                    ->schema([

                        Section::make('1st Shift Details')
                            ->schema([

                                TextEntry::make('shift1_time_in')
                                    ->time('h:i A') // Changed this
                                    ->label('Time In')
                                    ->placeholder('-'),
                                TextEntry::make('shift1_time_out')
                                    ->time('h:i A') // Changed this
                                    ->label('Time Out')
                                    ->placeholder('-'),

                            ])->columns(2),

                        Section::make('2nd Shift Details')
                            ->schema([

                                TextEntry::make('shift2_time_in')
                                    ->time('h:i A') // Changed this
                                    ->label('Time In')
                                    ->placeholder('-'),
                                TextEntry::make('shift2_time_out')
                                    ->time('h:i A') // Changed this
                                    ->label('Time Out')
                                    ->placeholder('-'),

                            ])->columns(2),

                        Section::make('3rd Shift Details')
                            ->schema([

                                TextEntry::make('shift3_time_in')
                                    ->time('h:i A') // Changed this
                                    ->label('Time In')
                                    ->placeholder('-'),
                                TextEntry::make('shift3_time_out')
                                    ->time('h:i A') // Changed this
                                    ->label('Time Out')
                                    ->placeholder('-'),

                            ])->columns(2),
                    ])->columns(3),

                Section::make('Summary Details')
                    ->schema([
                        TextEntry::make('overtime_hours')
                            ->numeric(),
                        TextEntry::make('undertime_hours')
                            ->numeric(),
                        TextEntry::make('total_hours')
                            ->numeric(),

                        TextEntry::make('night_diff_hours')
                            ->label('Night Differential Hours')
                            ->numeric(),

                        TextEntry::make('night_diff_ot_hours')
                            ->label('Night Differential OT Hours')
                            ->numeric(),

                        TextEntry::make('remarks')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Rest day' => 'gray',
                                'Absent With Pay' => 'danger',
                                'Legal Holiday' => 'success',
                                'Special Holiday' => 'warning',
                                default => 'info', // Default color for any other text
                            })
                            ->placeholder('-'),
                    ])->columns(4),

            ]);
    }
}
