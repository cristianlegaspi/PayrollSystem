<?php

namespace App\Filament\Resources\Contributions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ContributionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Personal Details')
                    ->schema([

                        // Branch selection
                     
                        Select::make('employee_id')
                            ->label('Employee')
                             ->relationship('employee', 'full_name')
                            ->required()
                            ->searchable()
                            ->placeholder('Select an employee'),

                    ])->columns(1),

                Section::make('SSS Contribution Details')
                    ->schema([
                        TextInput::make('sss_ee')
                            ->label('EE')
                            ->required()
                            ->numeric()
                            ->default(0.0),

                        TextInput::make('sss_er')
                            ->label('ER')
                            ->required()
                            ->numeric()
                            ->default(0.0),

                        TextInput::make('premium_voluntary_ss_contribution')
                            ->label('Premium Voluntary SS Contribution')
                            ->required()
                            ->numeric()
                            ->default(0.0),

                        TextInput::make('sss_salary_loan')
                            ->label('SSS Salary Loan')
                            ->required()
                            ->numeric()
                            ->default(0.0),

                        TextInput::make('sss_calamity_loan')
                            ->label('SSS Calamity Loan')
                            ->required()
                            ->numeric()
                            ->default(0.0),
                    ])->columns(3),

                Section::make('PHILHEALTH Contribution Details')
                    ->schema([
                        TextInput::make('philhealth_ee')
                            ->label('EE')
                            ->required()
                            ->numeric()
                            ->default(0.0),

                        TextInput::make('philhealth_er')
                            ->label('ER')
                            ->required()
                            ->numeric()
                            ->default(0.0),
                    ])->columns(2),

                Section::make('PAGIBIG Contribution Details')
                    ->schema([
                        TextInput::make('pagibig_ee')
                            ->label('EE')
                            ->required()
                            ->numeric()
                            ->default(0.0),

                        TextInput::make('pagibig_er')
                            ->label('ER')
                            ->required()
                            ->numeric()
                            ->default(0.0),

                        TextInput::make('pagibig_salary_loan')
                            ->label('Pagibig Salary Loan')
                            ->required()
                            ->numeric()
                            ->default(0.0),
                    ])->columns(3),

            ])->columns(1);
    }
}