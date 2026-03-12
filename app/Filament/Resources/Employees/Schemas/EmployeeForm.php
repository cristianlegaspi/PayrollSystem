<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_number')
                    ->placeholder(1)
                    ->unique()
                    ->required(),
                TextInput::make('full_name')
                    ->placeholder('Alfonso, Wilson C.')
                    ->required(),
                Select::make('position_id')
                    ->label('Position')
                    ->relationship('position', 'position_name')
                    ->required(),
                Select::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'branch_name')
                    ->required(),
                Select::make('employment_status_id')
                    ->label('Employment Status')
                    ->relationship('employmentStatus', 'name')
                    ->nullable(),
                Select::make('employment_type_id')
                    ->label('Employment Types')
                    ->relationship('employmentType', 'name')
                    ->required(),
                TextInput::make('daily_rate')
                    ->placeholder('600')
                    ->required()
                    ->numeric(),
                DatePicker::make('date_hired')
                    ->required(),
                DatePicker::make('date_of_birth'),
                TextInput::make('tin')
                    ->label('Tax Identification Number (TIN)')
                    ->placeholder('356-657-1234'),
                Select::make('status')
                    ->options(['Active' => 'Active', 'Inactive' => 'Inactive'])
                    ->default('Active')
                    ->required(),
            ]);
    }
}
