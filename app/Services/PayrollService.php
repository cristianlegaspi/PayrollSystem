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
            // Fetch DTRs for this employee & period
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
                $workedHours = $dtr->total_hours + $dtr->overtime_hours; // Total worked hours
                $undertimeHours = $dtr->undertime_hours ?? 0;

                $totalUndertimeHours += $undertimeHours;

                switch ($dtr->status) {
                    case 'legal_holiday':
                        // Legal Holiday: first 8 hours = regular (200%), excess = OT (260%)
                        $regularHours = min(8, $workedHours);
                        $otHours = max(0, $workedHours - 8);

                        $regularPay = $regularHours * ($hourlyRate * 2);       // 200% for legal holiday
                        $otPay      = $otHours * ($hourlyRate * 2.6);          // 260% for OT on legal holiday

                        $totalRegularSalary += $regularPay;
                        $totalOvertimeSalary += $otPay;
                        $daysWorked += 1;
                        break;

                    case 'special_holiday':
                        // Special Holiday: assume OT rate if worked
                        if ($workedHours > 0) {
                            $otPay = $workedHours * ($hourlyRate * 1.3); // 130% for special holiday
                            $totalOvertimeSalary += $otPay;
                            $daysWorked += 1;
                        }
                        break;

                    case 'rest_day':
                        if ($workedHours > 0) {
                            $restDayPay = $workedHours * ($hourlyRate * 1.3); // 130% Rest Day
                            $totalRestDaySalary += $restDayPay;
                            $daysWorked += 1;
                        }
                        break;

                    case 'on_duty':
                    case 'night_shift':
                        // Regular day with OT
                        $regularHours = min(8, $workedHours);
                        $otHours = max(0, $workedHours - 8);

                        $regularPay = $regularHours * $hourlyRate;
                        $otPay = $otHours * ($hourlyRate * 1.25); // OT 125%

                        $totalRegularSalary += $regularPay;
                        $totalOvertimeSalary += $otPay;
                        $daysWorked += 1;
                        break;

                    case 'absent_without_pay':
                        $daysAbsent += 1;
                        break;

                    case 'absent_with_pay':
                        $totalRegularSalary += $dailyRate; // Pay for absence with pay
                        $daysWorked += 1;
                        break;
                }

                // Sunday OT (if not already counted in status)
                if ($dtr->sunday_ot_hours > 0) {
                    $totalSundaySalary += $dtr->sunday_ot_hours * ($hourlyRate * 1.3);
                }

                // Night Differential
                if ($dtr->night_diff_hours > 0) {
                    $totalNightDiffSalary += $dtr->night_diff_hours * ($hourlyRate * 0.10);
                }

                if ($dtr->night_diff_ot_hours > 0) {
                    $totalNightDiffOtSalary += $dtr->night_diff_ot_hours * ($hourlyRate * 1.25 * 0.10);
                }
            }

            // Undertime deduction (regular hourly rate)
            $undertimeDeduction = $totalUndertimeHours * $hourlyRate;

            // Gross Pay
            $grossPay = $totalRegularSalary + $totalOvertimeSalary + $totalSundaySalary + $totalRestDaySalary + $totalNightDiffSalary + $totalNightDiffOtSalary - $undertimeDeduction;

            // ==========================================
            // Cutoff & Deductions Logic
            // ==========================================
            $startDay = Carbon::parse($period->start_date)->day;
            $isFirstCutoff = $startDay >= 1 && $startDay <= 15;
            $isSecondCutoff = $startDay >= 16;

            $totalDeductions = 0;

            if ($employee->contribution) {
                if ($isFirstCutoff) {
                    $totalDeductions += ($employee->contribution->sss_ee ?? 0);
                    $totalDeductions += ($employee->contribution->philhealth_ee ?? 0);
                    $totalDeductions += ($employee->contribution->pagibig_ee ?? 0);
                }
                if ($isSecondCutoff) {
                    $totalDeductions += ($employee->contribution->sss_salary_loan ?? 0);
                    $totalDeductions += ($employee->contribution->sss_calamity_loan ?? 0);
                    $totalDeductions += ($employee->contribution->pagibig_salary_loan ?? 0);
                }
            }

            // ==========================================
            // Create or Update Payroll Record
            // ==========================================
            Payroll::updateOrCreate(
                [
                    'employee_id'       => $employee->id,
                    'payroll_period_id' => $period->id,
                ],
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
                ]
            );
        }

        $period->update(['status' => 'Finalized', 'remarks' => 'Pending']);
    }
}