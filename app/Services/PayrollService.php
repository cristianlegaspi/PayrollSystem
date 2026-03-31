<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;
use App\Models\Payroll;
use Carbon\Carbon;

class PayrollService
{
    public function computePayrollForPeriod(PayrollPeriod $period)
    {
        // 1. Identify employees with records in this period
        $employeeIds = DailyTimeRecord::whereBetween('work_date', [$period->start_date, $period->end_date])
            ->pluck('employee_id')
            ->unique();

        $employees = Employee::with('contribution')
            ->whereIn('id', $employeeIds)
            ->get();

        foreach ($employees as $employee) {
            $dtrs = DailyTimeRecord::where('employee_id', $employee->id)
                ->whereBetween('work_date', [$period->start_date, $period->end_date])
                ->get();

            $dailyRate = (float) $employee->daily_rate;
            $hourlyRate = $dailyRate / 8;

            $daysWorked = 0;
            $daysAbsent = 0;
            $totalUndertimeHours = 0;

            $totalRegularSalary = 0;
            $totalOvertimeSalary = 0;
            $totalSundaySalary = 0;
            $totalRestDaySalary = 0;
            $totalNightDiffSalary = 0;
            $totalNightDiffOtSalary = 0;

            foreach ($dtrs as $dtr) {
                $workedHours = (float) ($dtr->total_hours + $dtr->overtime_hours);
                $undertimeHours = $dtr->undertime_hours ?? 0;
                $totalUndertimeHours += $undertimeHours;

                // Normalize remarks for easier checking
                $remarks = strtolower($dtr->remarks ?? '');

                // Handle Legal Holidays specifically based on your DTR example
                if (str_contains($remarks, 'legal holiday')) {
                    if (str_contains($remarks, 'no work')) {
                        // 100% Pay even with 0 hours worked
                        $totalRegularSalary += $dailyRate;
                    } else {
                        // Worked on Legal Holiday: 200% for first 8 hours, 260% for OT
                        $regHrs = min(8, $workedHours);
                        $otHrs = max(0, $workedHours - 8);
                        
                        $totalRegularSalary += ($regHrs * ($hourlyRate * 2));
                        $totalOvertimeSalary += ($otHrs * ($hourlyRate * 2.6));
                    }
                    $daysWorked += 1;
                    continue; // Skip the switch for this DTR row
                }

                switch ($dtr->status) {
                    case 'special_holiday':
                        if ($workedHours > 0) {
                            // Special Holiday Worked: 130%
                            $totalOvertimeSalary += $workedHours * ($hourlyRate * 1.3);
                            $daysWorked += 1;
                        }
                        break;

                    case 'rest_day':
                        if ($workedHours > 0) {
                            $totalRestDaySalary += $workedHours * ($hourlyRate * 1.3);
                            $daysWorked += 1;
                        }
                        break;

                    case 'on_duty':
                    case 'night_shift':
                        $regHrs = min(8, $workedHours);
                        $otHrs = max(0, $workedHours - 8);

                        $totalRegularSalary += ($regHrs * $hourlyRate);
                        $totalOvertimeSalary += ($otHrs * ($hourlyRate * 1.25));
                        $daysWorked += 1;
                        break;

                    case 'absent_without_pay':
                        $daysAbsent += 1;
                        break;

                    case 'absent_with_pay':
                        $totalRegularSalary += $dailyRate;
                        $daysWorked += 1;
                        break;
                }

                // Add-ons (Sunday & Night Diff)
                if ($dtr->sunday_ot_hours > 0) {
                    $totalSundaySalary += $dtr->sunday_ot_hours * ($hourlyRate * 1.3);
                }

                if ($dtr->night_diff_hours > 0) {
                    $totalNightDiffSalary += $dtr->night_diff_hours * ($hourlyRate * 0.10);
                }

                if ($dtr->night_diff_ot_hours > 0) {
                    $totalNightDiffOtSalary += $dtr->night_diff_ot_hours * ($hourlyRate * 1.25 * 0.10);
                }
            }

            // Deductions
            $undertimeDeduction = $totalUndertimeHours * $hourlyRate;
            $grossPay = ($totalRegularSalary + $totalOvertimeSalary + $totalSundaySalary + 
                         $totalRestDaySalary + $totalNightDiffSalary + $totalNightDiffOtSalary) - $undertimeDeduction;

            $startDay = Carbon::parse($period->start_date)->day;
            $isFirstCutoff = $startDay >= 1 && $startDay <= 15;
            $isSecondCutoff = $startDay >= 16;

            $totalDeductions = 0;
            if ($employee->contribution) {
                if ($isFirstCutoff) {
                    $totalDeductions += ($employee->contribution->sss_ee ?? 0);
                    $totalDeductions += ($employee->contribution->philhealth_ee ?? 0);
                    $totalDeductions += ($employee->contribution->pagibig_ee ?? 0);
                } else {
                    $totalDeductions += ($employee->contribution->sss_salary_loan ?? 0);
                    $totalDeductions += ($employee->contribution->sss_calamity_loan ?? 0);
                    $totalDeductions += ($employee->contribution->pagibig_salary_loan ?? 0);
                }
            }

            Payroll::updateOrCreate(
                ['employee_id' => $employee->id, 'payroll_period_id' => $period->id],
                [
                    'days_worked'           => $daysWorked,
                    'days_absent'           => $daysAbsent,
                    'daily_rate'            => $dailyRate,
                    'undertime_hours'       => $totalUndertimeHours,
                    'undertime_deduction'   => round($undertimeDeduction, 2),
                    'basic_salary'          => round($totalRegularSalary, 2),
                    'overtime_hours'        => $dtrs->sum('overtime_hours'),
                    'overtime_salary'       => round($totalOvertimeSalary, 2),
                    'sunday_ot_hours'       => $dtrs->sum('sunday_ot_hours'),
                    'sunday_ot_salary'      => round($totalSundaySalary, 2),
                    'rest_day_ot_hours'     => $dtrs->sum('rest_day_ot_hours'),
                    'rest_day_ot_salary'    => round($totalRestDaySalary, 2),
                    'night_diff_hours'      => $dtrs->sum('night_diff_hours'),
                    'night_diff_salary'     => round($totalNightDiffSalary, 2),
                    'night_diff_ot_hours'   => $dtrs->sum('night_diff_ot_hours'),
                    'night_diff_ot_salary'  => round($totalNightDiffOtSalary, 2),
                    'gross_pay'             => round($grossPay, 2),
                    'total_deductions'      => round($totalDeductions, 2),
                    'net_pay'               => round($grossPay - $totalDeductions, 2),
                ]
            );
        }

        $period->update(['status' => 'Finalized']);
    }
}