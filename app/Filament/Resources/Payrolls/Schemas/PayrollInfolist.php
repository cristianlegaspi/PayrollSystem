<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;

class PayrollInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([


             Section::make('Employment Details')
                    ->schema([
                 TextEntry::make('employee.full_name')
                    ->numeric(),

                 TextEntry::make('daily_rate')
                    ->numeric(),

             ])->columns(2),


              Section::make('Payroll Period Details')
                    ->schema([
                 TextEntry::make('payrollPeriod.description')
                    ->numeric(),

             ]),

             
              Section::make('Attendance Record Details')
                    ->schema([
                  TextEntry::make('days_worked')
                    ->numeric(),
                TextEntry::make('days_absent')
                    ->numeric(),
                TextEntry::make('undertime_hours')
                    ->numeric(),
                TextEntry::make('overtime_hours')
                    ->numeric(),
                TextEntry::make('night_diff_hours')
                    ->numeric(),
                TextEntry::make('night_diff_ot_hours')
                    ->label('Night Differential OT Hours')
                    ->numeric(),
                TextEntry::make('sunday_ot_hours')
                    ->label('Sunday OT Hours')
                    ->numeric(),



             TextEntry::make('rest_day_ot_hours')
                    ->label('Rest Day OT Hours')
                    ->numeric(),
           
           
             ])->columns(2),



             Section::make('Salary Details')
                    ->schema([

                     TextEntry::make('basic_salary')
                    ->numeric(),
                TextEntry::make('overtime_salary')
                    ->label('Regular OT Salary')
                    ->numeric(),
                TextEntry::make('night_diff_salary')
                    ->numeric(),
                TextEntry::make('night_diff_ot_salary')
                    ->label('Night Differential OT Salary')
                    ->numeric(),
                TextEntry::make('sunday_ot_salary')
                    ->label('Sunday OT Salary')
                    ->numeric(),

                 TextEntry::make('rest_day_ot_salary')
                    ->label('Rest Day OT Salary')
                    ->numeric(),

                  TextEntry::make('undertime_deduction')
                    ->label('Undertime Deduction')
                    ->numeric(),



                    
                TextEntry::make('gross_pay')
                    ->numeric(),
               TextEntry::make('total_deductions')
                            ->label('Total Deductions')
                            ->state(function (Model $record): float {
                                return ($record->total_deductions ?? 0) + 
                                       ($record->cash_advance ?? 0) + 
                                       ($record->other_deduction ?? 0) + 
                                       ($record->shortages ?? 0);
                            })
                            ->money('PHP')
                            ->color('danger')
                            ->weight('bold'),
                TextEntry::make('cash_advance')
                    ->numeric(),
                TextEntry::make('shortages')
                    ->numeric(),
                 TextEntry::make('other_deduction')
                    ->numeric(),
                TextEntry::make('net_pay')
                    ->numeric()
                    ->badge(),
                

             ])->columns(3),



            ]);
    }
}
