<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\DailyTimeRecord;
use App\Models\Payroll;

class PayrollService
{
    public function computePayrollForPeriod(PayrollPeriod $period)
    {
        // Get employees who have DTRs in this period
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

            // Attendance summary
            $daysWorked = $dtrs->whereIn('status', ['on_duty', 'absent_with_pay'])->count();
            $daysAbsent = $dtrs->where('status', 'absent_without_pay')->count();

            // Undertime only for actual worked days
            $undertimeHours = $dtrs
                ->filter(fn($dtr) => $dtr->status === 'on_duty')
                ->sum('undertime_hours');

            // Hourly rate
            $dailyRate = (float) $employee->daily_rate;
            $hourlyRate = $dailyRate / 8;

            // Basic salary
            $basicSalary = $dailyRate * $daysWorked;

            // Deduct undertime
            $undertimeDeduction = $undertimeHours * $hourlyRate;
            $basicSalaryAfterUndertime = $basicSalary - $undertimeDeduction;

            // OT / ND computations
            $overtimeHours = $dtrs->whereIn('status', ['on_duty', 'absent_with_pay'])->sum('overtime_hours');
            $nightDiffHours = $dtrs->whereIn('status', ['on_duty', 'absent_with_pay'])->sum('night_diff_hours');
            $nightDiffOtHours = $dtrs->whereIn('status', ['on_duty', 'absent_with_pay'])->sum('night_diff_ot_hours');

            $overtimeSalary = $overtimeHours * ($hourlyRate * 1.25);
            $nightDiffSalary = $nightDiffHours * ($hourlyRate * 0.10);
            $nightDiffOtSalary = $nightDiffOtHours * ($hourlyRate * 0.25);

            // Gross pay includes undertime deduction
            $grossPay = $basicSalaryAfterUndertime + $overtimeSalary + $nightDiffSalary + $nightDiffOtSalary;

            // Government contributions
            $contribution = $employee->contribution;
            $totalDeductions = 0;

            if ($contribution) {
                $totalDeductions =
                    ($contribution->sss_ee ?? 0) +
                    ($contribution->philhealth_ee ?? 0) +
                    ($contribution->pagibig_ee ?? 0) +
                    ($contribution->sss_salary_loan ?? 0) +
                    ($contribution->sss_calamity_loan ?? 0) +
                    ($contribution->pagibig_loan ?? 0) +
                    ($contribution->pagibig_salary_loan ?? 0) +
                    ($contribution->premium_voluntary_ss_contribution ?? 0);
            }

            // Preserve manual edits
            $existingPayroll = Payroll::where('employee_id', $employee->id)
                ->where('payroll_period_id', $period->id)
                ->first();

            $cashAdvance = $existingPayroll->cash_advance ?? 0;
            $shortages = $existingPayroll->shortages ?? 0;

            $totalDeductionsWithManual = $totalDeductions + $cashAdvance + $shortages;

            // Net Pay
            $netPay = $grossPay - $totalDeductionsWithManual;

            // Save payroll data
            $payrollData = [
                'employee_id' => $employee->id,
                'payroll_period_id' => $period->id,
                'days_worked' => $daysWorked,
                'days_absent' => $daysAbsent,
                'undertime_hours' => $undertimeHours,
                'undertime_deduction' => round($undertimeDeduction, 2),
                'overtime_hours' => $overtimeHours,
                'night_diff_hours' => $nightDiffHours,
                'night_diff_ot_hours' => $nightDiffOtHours,
                'daily_rate' => $dailyRate,
                'basic_salary' => round($basicSalaryAfterUndertime, 2),
                'overtime_salary' => round($overtimeSalary, 2),
                'night_diff_salary' => round($nightDiffSalary, 2),
                'night_diff_ot_salary' => round($nightDiffOtSalary, 2),
                'gross_pay' => round($grossPay, 2),

                'cash_advance' => $cashAdvance,
                'shortages' => $shortages,

                'total_deductions' => round($totalDeductionsWithManual, 2),
                'net_pay' => round($netPay, 2),
            ];

            Payroll::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'payroll_period_id' => $period->id,
                ],
                $payrollData
            );
        }

        // ✅ FINAL STEP: UPDATE PAYROLL PERIOD STATUS
        $period->update([
            'status' => 'finalized'
        ]);
    }
}