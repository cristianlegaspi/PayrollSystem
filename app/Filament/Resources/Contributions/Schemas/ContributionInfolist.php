<?php

namespace App\Filament\Resources\Contributions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ContributionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Employee Details')
                    ->schema([

                        TextEntry::make('employee.full_name')
                            ->numeric(),
                    ])->columns(1),


                Section::make('SSS Contribution Details')
                    ->schema([

                        TextEntry::make('sss_ee')
                            ->label('EE'),


                        TextEntry::make('sss_er')
                            ->label('ER'),

                        TextEntry::make('premium_voluntary_ss_contribution')
                            ->label('Premium SS Contribution'),




                        TextEntry::make('sss_salary_loan')
                            ->label('SSS Salary Loan'),
                        TextEntry::make('sss_calamity_loan')
                            ->label('SSS Calamity Loan'),

                    ])->columns(5),



                Section::make('PHILHEALTH Contribution Details')
                    ->schema([

                        TextEntry::make('philhealth_ee')
                            ->label('PHILHEALTH EE'),
                        TextEntry::make('philhealth_er')
                            ->label('PHILHEALTH ER'),





                    ])->columns(2),


                Section::make('PAGIBIG Contribution Details')
                    ->schema([

                        TextEntry::make('pagibig_ee')
                            ->label('PAGIBIG EE'),
                        TextEntry::make('pagibig_er')
                            ->label('PAGIBIG ER'),


                        TextEntry::make('pagibig_salary_loan')
                            ->label('PAGIBIG Salary Loan'),

                    ])->columns(3),

            ]);
    }
}
