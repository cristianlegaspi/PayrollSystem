<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use App\Models\DailyTimeRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;

class PayrollInfolist
{
    /**
     * Separate regular and holiday salary.
     *
     * Assumes basic_salary contains both amounts and
     * each holiday credit equals one daily rate.
     */
    private static function salaryBreakdown(Model $record): array
    {
        $dailyRate = (float) ($record->daily_rate ?? 0);
        $combinedSalary = (float) ($record->basic_salary ?? 0);

        $totalPaidDays = $dailyRate > 0
            ? max(0, (int) round($combinedSalary / $dailyRate))
            : 0;

        $holidayCount = 0;

        if (
            $record->employee_id
            && $record->payrollPeriod?->start_date
            && $record->payrollPeriod?->end_date
        ) {
            $holidayCount = DailyTimeRecord::query()
                ->where('employee_id', $record->employee_id)
                ->whereBetween('work_date', [
                    $record->payrollPeriod->start_date,
                    $record->payrollPeriod->end_date,
                ])
                ->where(function ($query) {
                    $query
                        ->where('remarks', 'LIKE', '%Legal Holiday%')
                        ->orWhere('status', 'LIKE', '%legal_holiday%');
                })
                ->count();
        }

        // Maintain the same logic as the existing payslip.
        if (
            $totalPaidDays === 15
            || ($holidayCount > 0 && $totalPaidDays >= $holidayCount)
        ) {
            $holidayDays = $holidayCount > 0
                ? $holidayCount
                : 1;

            $holidayDays = min($holidayDays, $totalPaidDays);
        } else {
            $holidayDays = 0;
        }

        $regularDays = max(0, $totalPaidDays - $holidayDays);

        $holidaySalary = round($holidayDays * $dailyRate, 2);
        $regularSalary = round($combinedSalary - $holidaySalary, 2);

        return [
            'regular_days' => $regularDays,
            'holiday_days' => $holidayDays,
            'basic_salary' => $regularSalary,
            'holiday_salary' => $holidaySalary,
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Employment Details')
                    ->schema([
                        TextEntry::make('employee.full_name')
                            ->label('Employee Name'),

                        TextEntry::make('daily_rate')
                            ->label('Daily Rate')
                            ->money('PHP'),
                    ])
                    ->columns(2),

                Section::make('Payroll Period Details')
                    ->schema([
                        TextEntry::make('payrollPeriod.description')
                            ->label('Payroll Period'),
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
                    ])
                    ->columns(2),

                Section::make('Salary Details')
                    ->schema([

                        // REGULAR BASIC SALARY
                        TextEntry::make('regular_basic_salary')
                            ->label(fn (Model $record) =>
                                'Basic Salary (' .
                                self::salaryBreakdown($record)['regular_days'] .
                                ' days)'
                            )
                            ->state(fn (Model $record) =>
                                self::salaryBreakdown($record)['basic_salary']
                            )
                            ->money('PHP'),

                        // HOLIDAY SALARY
                        TextEntry::make('holiday_salary_amount')
                            ->label(fn (Model $record) =>
                                'Holiday Salary (' .
                                self::salaryBreakdown($record)['holiday_days'] .
                                ' days)'
                            )
                            ->state(fn (Model $record) =>
                                self::salaryBreakdown($record)['holiday_salary']
                            )
                            ->money('PHP'),

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
                            ->label('Gross Pay')
                            ->money('PHP'),

                        TextEntry::make('total_deductions')
                            ->label('Total Deductions')
                            ->state(function (Model $record): float {
                                return (float) ($record->total_deductions ?? 0)
                                    + (float) ($record->cash_advance ?? 0)
                                    + (float) ($record->other_deduction ?? 0)
                                    + (float) ($record->shortages ?? 0);
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

                        TextEntry::make('other_incentives')
                            ->numeric(),

                        TextEntry::make('net_pay')
                            ->money('PHP')
                            ->badge(),
                    ])
                    ->columns(2),

            ]);
    }
}
