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
     * Special holiday premium rate (30% of the daily rate).
     * Change this if the special holiday rate rule changes.
     */
    private const SPECIAL_HOLIDAY_RATE = 0.30;

    /**
     * Cache so the DTR queries run once per record per request.
     */
    private static array $breakdownCache = [];

    /**
     * Separate regular salary and holiday salary.
     *
     * basic_salary is assumed to contain both amounts.
     *
     * LEGAL HOLIDAY   : holiday salary = 1 daily rate per legal holiday day.
     * SPECIAL HOLIDAY : holiday salary = ONLY the premium (daily rate x 30%).
     *                   The normal daily rate for that day stays in Basic
     *                   Salary and the day counts as a worked day.
     *
     * Example: 700 daily rate, worked on a special holiday
     *          saved basic_salary = 910
     *          -> Basic Salary   = 700
     *          -> Holiday Salary = 210
     */
    private static function salaryBreakdown(Model $record): array
    {
        $cacheKey = $record->getKey() . '|' . ($record->updated_at ?? '');

        if (isset(self::$breakdownCache[$cacheKey])) {
            return self::$breakdownCache[$cacheKey];
        }

        $dailyRate = (float) ($record->daily_rate ?? 0);
        $combinedSalary = (float) ($record->basic_salary ?? 0);

        $legalHolidaysCount = 0;
        $specialHolidaysCount = 0;

        if (
            $record->employee_id
            && $record->payrollPeriod?->start_date
            && $record->payrollPeriod?->end_date
        ) {
            $start = $record->payrollPeriod->start_date;
            $end = $record->payrollPeriod->end_date;

            // Legal holiday days recorded in the DTR.
            $legalHolidaysCount = DailyTimeRecord::query()
                ->where('employee_id', $record->employee_id)
                ->whereBetween('work_date', [$start, $end])
                ->where(function ($query) {
                    $query
                        ->where('remarks', 'LIKE', '%Legal Holiday%')
                        ->orWhere('status', 'LIKE', '%legal_holiday%');
                })
                ->count();

            // Special holiday days recorded in the DTR.
            $specialHolidaysCount = DailyTimeRecord::query()
                ->where('employee_id', $record->employee_id)
                ->whereBetween('work_date', [$start, $end])
                ->where(function ($query) {
                    $query
                        ->where('remarks', 'LIKE', '%Special Holiday%')
                        ->orWhere('remarks', 'LIKE', '%Special Non-Working%')
                        ->orWhere('status', 'LIKE', '%special_holiday%')
                        ->orWhere('status', 'LIKE', '%special_non_working%');
                })
                ->count();
        }

        // Special holiday premium only (e.g. 700 x 30% = 210).
        $specialHolidaySalary = ($dailyRate > 0 && $specialHolidaysCount > 0)
            ? min(
                $combinedSalary,
                round($specialHolidaysCount * $dailyRate * self::SPECIAL_HOLIDAY_RATE, 2)
            )
            : 0;

        // Remove the special premium first so it is not counted as an
        // extra paid day (910 - 210 = 700 -> 1 paid day, not 1.3).
        $salaryWithoutSpecial = $combinedSalary - $specialHolidaySalary;

        $totalPaidDays = $dailyRate > 0
            ? max(0, (int) round($salaryWithoutSpecial / $dailyRate))
            : 0;

        // Legal holiday days are paid days that were not worked.
        $legalHolidayDays = ($legalHolidaysCount > 0 && $totalPaidDays > 0)
            ? min($legalHolidaysCount, $totalPaidDays)
            : 0;

        // Special holiday days are worked days, so they stay in regular days.
        $specialHolidayDays = ($specialHolidaysCount > 0 && $totalPaidDays > 0)
            ? min($specialHolidaysCount, max(0, $totalPaidDays - $legalHolidayDays))
            : 0;

        $regularDays = max(0, $totalPaidDays - $legalHolidayDays);

        // Legal holiday pay = full daily rate per holiday day.
        $legalHolidaySalary = round($legalHolidayDays * $dailyRate, 2);

        // Holiday Salary = legal holiday pay + special holiday premium only.
        $holidaySalary = min(
            $combinedSalary,
            round($legalHolidaySalary + $specialHolidaySalary, 2)
        );

        // Basic salary = saved salary minus the holiday component.
        $regularSalary = round($combinedSalary - $holidaySalary, 2);

        return self::$breakdownCache[$cacheKey] = [
            'regular_days'         => $regularDays,
            'holiday_days'         => $legalHolidayDays + $specialHolidayDays,
            'legal_holiday_days'   => $legalHolidayDays,
            'special_holiday_days' => $specialHolidayDays,
            'basic_salary'         => $regularSalary,
            'holiday_salary'       => $holidaySalary,
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

                        // HOLIDAY SALARY (legal holiday pay + special holiday premium only)
                        TextEntry::make('holiday_salary_amount')
                            ->label(function (Model $record) {
                                $breakdown = self::salaryBreakdown($record);

                                $label = 'Holiday Salary (' . $breakdown['holiday_days'] . ' days';

                                $parts = [];

                                if ($breakdown['legal_holiday_days'] > 0) {
                                    $parts[] = $breakdown['legal_holiday_days'] . ' legal';
                                }

                                if ($breakdown['special_holiday_days'] > 0) {
                                    $parts[] = $breakdown['special_holiday_days'] . ' special';
                                }

                                if (! empty($parts)) {
                                    $label .= ': ' . implode(', ', $parts);
                                }

                                return $label . ')';
                            })
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