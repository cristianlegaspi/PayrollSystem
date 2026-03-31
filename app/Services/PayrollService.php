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
            // Fetch DTRs for this specific employee and period
            $dtrs = DailyTimeRecord::where('employee_id', $employee->id)
                ->whereBetween('work_date', [$period->start_date, $period->end_date])
                ->get();

            // ==========================================
            // 2. Attendance & Basic Salary
            // ==========================================
            
            /**
             * logic: Count days where employee is present or entitled to pay.
             * includes 'On Duty', 'On Duty w/ OT', and 'Legal Holiday'.
             */
            $daysWorked = $dtrs->whereIn('remarks', [
                'On Duty', 
                'On Duty w/ OT', 
                'Legal Holiday'
            ])->count();

            /**
             * logic: Count specific absent remarks to ensure they match your DTR strings.
             */
            $daysAbsent = $dtrs->whereIn('remarks', [
                'Absent', 
                'Absent Without Pay'
            ])->count();
            
            // Aligning with your DTR Model column name: 'undertime_hours'
            $totalUndertimeHours = $dtrs->sum('undertime_hours');

            $dailyRate = (float) $employee->daily_rate; 
            $hourlyRate = $dailyRate / 8;               

            // Basic Salary Computation
            $basicSalary = $dailyRate * $daysWorked;
            $undertimeDeduction = $totalUndertimeHours * $hourlyRate;
            $basicSalaryAfterUndertime = $basicSalary - $undertimeDeduction;

            // ==========================================
            // 3. OT & Premium Computation
            // ==========================================
            $sumOvertimeHours    = $dtrs->sum('overtime_hours');
            $sumSundayOtHours    = $dtrs->sum('sunday_ot_hours');
            $sumRestDayOtHours   = $dtrs->sum('rest_day_ot_hours');
            $sumNightDiffHours   = $dtrs->sum('night_diff_hours');
            $sumNightDiffOtHours = $dtrs->sum('night_diff_ot_hours');

            // Regular Overtime (1.25x)
            $overtimeSalary = $sumOvertimeHours * ($hourlyRate * 1.25);
            
            // Sunday & Rest Day Premium (1.30x)
            $sundayOtSalary  = $sumSundayOtHours * ($hourlyRate * 1.30);
            $restDayOtSalary = $sumRestDayOtHours * ($hourlyRate * 1.30);
            
            // Night Differential (10% premium)
            $nightDiffSalary   = $sumNightDiffHours * ($hourlyRate * 0.10);
            $nightDiffOtSalary = $sumNightDiffOtHours * (($hourlyRate * 1.25) * 0.10);

            // Gross Pay Calculation
            $grossPay = $basicSalaryAfterUndertime + $overtimeSalary + $sundayOtSalary + $restDayOtSalary + $nightDiffSalary + $nightDiffOtSalary;

            // ==========================================
            // 4. Cutoff & Deductions Logic
            // ==========================================
            $startDay = Carbon::parse($period->start_date)->day;
            $isFirstCutoff = $startDay >= 1 && $startDay <= 15;
            $isSecondCutoff = $startDay >= 16;

            $totalDeductions = 0;

            if ($employee->contribution) {
                // First Cutoff: Government Mandated
                if ($isFirstCutoff) {
                    $totalDeductions += ($employee->contribution->sss_ee ?? 0);
                    $totalDeductions += ($employee->contribution->philhealth_ee ?? 0);
                    $totalDeductions += ($employee->contribution->pagibig_ee ?? 0);
                }
                // Second Cutoff: Loans
                if ($isSecondCutoff) {
                    $totalDeductions += ($employee->contribution->sss_salary_loan ?? 0);
                    $totalDeductions += ($employee->contribution->sss_calamity_loan ?? 0);
                    $totalDeductions += ($employee->contribution->pagibig_salary_loan ?? 0);
                }
            }

            // ==========================================
            // 5. Create or Update Payroll Record
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
                    
                    'basic_salary'          => round($basicSalaryAfterUndertime, 2),
                    
                    'overtime_hours'        => $sumOvertimeHours,
                    'overtime_salary'       => round($overtimeSalary, 2),
                    
                    'sunday_ot_hours'       => $sumSundayOtHours,
                    'sunday_ot_salary'      => round($sundayOtSalary, 2),
                    
                    'rest_day_ot_hours'     => $sumRestDayOtHours,
                    'rest_day_ot_salary'    => round($restDayOtSalary, 2),
                    
                    'night_diff_hours'      => $sumNightDiffHours,
                    'night_diff_salary'     => round($nightDiffSalary, 2),
                    
                    'night_diff_ot_hours'   => $sumNightDiffOtHours,
                    'night_diff_ot_salary'  => round($nightDiffOtSalary, 2),
                    
                    'gross_pay'             => round($grossPay, 2),
                    'total_deductions'      => round($totalDeductions, 2),
                ]
            );
        }

       $period->update(['status' => 'Finalized', 'remarks' => 'Pending']);                 
    }
}