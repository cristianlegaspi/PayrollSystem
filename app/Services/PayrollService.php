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

            // Counters
            $daysWorked = 0;
            $daysAbsent = 0;
            $totalUndertimeHours = 0;

            // Salary Buckets
            $totalRegularSalary = 0;
            $totalOvertimeSalary = 0;
            $totalSundaySalary = 0;
            $totalRestDaySalary = 0;
            $totalNightDiffSalary = 0;
            $totalNightDiffOtSalary = 0;

            foreach ($dtrs as $dtr) {
                $remarks = strtolower($dtr->remarks ?? '');
                $status = strtolower($dtr->status ?? '');
                
                // MAPPED TO YOUR EXACT DTR MODEL FIELDS
                $otHrs = (float) ($dtr->overtime_hours ?? 0);       
                $rdOtHrs = (float) ($dtr->rest_day_ot_hours ?? 0);     
                $sunOtHrs = (float) ($dtr->sunday_ot_hours ?? 0);   
                $ndHrs = (float) ($dtr->night_diff_hours ?? 0);
                $ndOtHrs = (float) ($dtr->night_diff_ot_hours ?? 0);
                $utHrs = (float) ($dtr->undertime_hours ?? 0);
                $totalHrs = (float) ($dtr->total_hours ?? 0);
                
                $totalUndertimeHours += $utHrs;

                // --- 1. REST DAY OT LOGIC (Marlon Mar 23 Fix) ---
                if ($rdOtHrs > 0) {
                    $totalRestDaySalary += ($rdOtHrs * ($hourlyRate * 1.3));
                    $daysWorked += 1;
                } 
                // --- 2. LEGAL HOLIDAY LOGIC (Marlon Mar 20 Fix) ---
                elseif (str_contains($remarks, 'legal holiday')) {
                    // Double basic pay (8 hrs)
                    $totalRegularSalary += ($dailyRate * 2);
                    // Holiday OT is usually 260% of hourly rate
                    if ($otHrs > 0) {
                        $totalOvertimeSalary += ($otHrs * ($hourlyRate * 2.6));
                    }
                    $daysWorked += 1;
                }
                // --- 3. SPECIAL HOLIDAY LOGIC ---
                elseif ($status === 'special_holiday' && $totalHrs > 0) {
                    $totalRegularSalary += ($dailyRate * 1.3);
                    $totalOvertimeSalary += ($otHrs * ($hourlyRate * 1.69));
                    $daysWorked += 1;
                }
                // --- 4. NORMAL WORKING DAY ---
                elseif ($totalHrs > 0 || $status === 'on_duty' || $status === 'night_shift') {
                    $totalRegularSalary += $dailyRate;
                    $totalOvertimeSalary += ($otHrs * ($hourlyRate * 1.25));
                    $daysWorked += 1;
                } 
                // --- 5. ABSENCE LOGIC ---
                elseif ($status === 'absent_without_pay') {
                    $daysAbsent += 1;
                }

                // --- 6. ADD-ONS (Sunday & Night Diff) ---
                if ($sunOtHrs > 0) {
                    $totalSundaySalary += ($sunOtHrs * ($hourlyRate * 0.30));
                }
                if ($ndHrs > 0) {
                    $totalNightDiffSalary += ($ndHrs * ($hourlyRate * 0.10));
                }
                if ($ndOtHrs > 0) {
                    $totalNightDiffOtSalary += ($ndOtHrs * ($hourlyRate * 1.25 * 0.10));
                }
            }

            // Calculations
            $undertimeDeduction = $totalUndertimeHours * $hourlyRate;
            
            $grossPay = ($totalRegularSalary + 
                         $totalOvertimeSalary + 
                         $totalSundaySalary + 
                         $totalRestDaySalary + 
                         $totalNightDiffSalary + 
                         $totalNightDiffOtSalary) - $undertimeDeduction;

            // Simple deduction logic from contribution model
            $totalDeductions = 0;
            if ($employee->contribution) {
                $startDay = Carbon::parse($period->start_date)->day;
                if ($startDay <= 15) {
                    $totalDeductions += ($employee->contribution->sss_ee ?? 0);
                    $totalDeductions += ($employee->contribution->philhealth_ee ?? 0);
                    $totalDeductions += ($employee->contribution->pagibig_ee ?? 0);
                } else {
                    $totalDeductions += ($employee->contribution->sss_salary_loan ?? 0);
                    $totalDeductions += ($employee->contribution->pagibig_salary_loan ?? 0);
                }
            }

            // Final Update/Create for the Payroll Table
            Payroll::updateOrCreate(
                ['employee_id' => $employee->id, 'payroll_period_id' => $period->id],
                [
                    'days_worked'           => $daysWorked,
                    'days_absent'           => $daysAbsent,
                    'undertime_hours'       => $totalUndertimeHours,
                    'overtime_hours'        => $dtrs->sum('overtime_hours'),
                    'night_diff_hours'      => $dtrs->sum('night_diff_hours'),
                    'night_diff_ot_hours'   => $dtrs->sum('night_diff_ot_hours'),
                    'daily_rate'            => $dailyRate,
                    'basic_salary'          => round($totalRegularSalary, 2),
                    'overtime_salary'       => round($totalOvertimeSalary, 2),
                    'night_diff_salary'     => round($totalNightDiffSalary, 2),
                    'night_diff_ot_salary'  => round($totalNightDiffOtSalary, 2),
                    'gross_pay'             => round($grossPay, 2),
                    'total_deductions'      => round($totalDeductions, 2),
                    'net_pay'               => round($grossPay - $totalDeductions, 2),
                    'sunday_ot_hours'       => $dtrs->sum('sunday_ot_hours'),
                    'sunday_ot_salary'      => round($totalSundaySalary, 2),
                    'undertime_deduction'   => round($undertimeDeduction, 2),
                    'rest_day_ot_hours'     => $dtrs->sum('rest_day_ot_hours'), 
                    'rest_day_ot_salary'    => round($totalRestDaySalary, 2),
                ]
            );
        }

        $period->update(['status' => 'Finalized']);
    }
}