<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Personal Details')
                    ->schema([

                        TextEntry::make('employee_number'),
                        TextEntry::make('full_name'),
                        TextEntry::make('date_of_birth')
                            ->date()
                            ->label('Date of Birth')
                            ->placeholder('-'),

                        TextEntry::make('tin')
                            ->label('Tax Identification Number')
                            ->placeholder('-'),

                    ])->columns(4),


                Section::make('Employment Details')
                    ->schema([

                        TextEntry::make('position.position_name')
                            ->label('Position')
                            ->numeric(),

                        TextEntry::make('branch.branch_name')
                            ->label('Branch')
                            ->numeric(),

                        TextEntry::make('employmentStatus.name')
                            ->label('Employment Status')
                            ->numeric(),


                        TextEntry::make('employmentType.name')
                            ->label('Employment Types')
                            ->badge(),

                        TextEntry::make('daily_rate')
                            ->label('Daily Rate')
                            ->numeric(),

                        TextEntry::make('date_hired')

                            ->label('Date Hired')
                            ->date(),
                           

                        TextEntry::make('status')
                            ->badge(),
                    ])->columns(4),
            ])->columns(1);
    }
}
