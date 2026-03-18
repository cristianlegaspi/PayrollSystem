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

            // =====================
            // Attendance Computation
            // =====================
            $daysWorked = $dtrs->whereIn('status', ['on_duty', 'absent_with_pay'])->count();
            $daysAbsent = $dtrs->where('status', 'absent_without_pay')->count();
            $undertimeHours = $dtrs->where('status', 'on_duty')->sum('undertime_hours');

            $dailyRate = (float) $employee->daily_rate;
            $hourlyRate = $dailyRate / 8;

            $basicSalary = $dailyRate * $daysWorked;
            $undertimeDeduction = $undertimeHours * $hourlyRate;
            $basicSalaryAfterUndertime = $basicSalary - $undertimeDeduction;

            // =====================
            // OT + Night Diff Computation
            // =====================
            $overtimeSalary = 0;
            $sundayOtSalary = 0;
            $nightDiffSalary = 0;
            $nightDiffOtSalary = 0;

            foreach ($dtrs as $dtr) {
                $overtimeSalary += $dtr->overtime_hours * ($hourlyRate * 1.25);
                $sundayOtSalary += $dtr->sunday_ot_hours * ($hourlyRate * 1.30);
                $nightDiffSalary += $dtr->night_diff_hours * ($hourlyRate * 0.10);
                $nightDiffOtSalary += $dtr->night_diff_ot_hours * (($hourlyRate * 1.25) * 0.10);
            }

            $grossPay = $basicSalaryAfterUndertime + $overtimeSalary + $sundayOtSalary + $nightDiffSalary + $nightDiffOtSalary;

            // =====================
            // Determine cutoff
            // =====================
            $startDay = Carbon::parse($period->start_date)->day;
            $isFirstCutoff = $startDay >= 1 && $startDay <= 15;
            $isSecondCutoff = $startDay >= 16;

            // =====================
            // Contributions & Loans
            // =====================
            $deductions = [];
            $totalDeductions = 0;
            $manualFields = ['cash_advance', 'shortages', 'other_deduction'];

            $existingPayroll = Payroll::where('employee_id', $employee->id)
                ->where('payroll_period_id', $period->id)
                ->first();

            // First cutoff: government contributions
            if ($isFirstCutoff && $employee->contribution) {
                foreach (['sss_ee', 'philhealth_ee', 'pagibig_ee', 'premium_voluntary_ss_contribution'] as $field) {
                    if (!empty($employee->contribution->$field)) {
                        $deductions[$field] = $employee->contribution->$field;
                        $totalDeductions += $employee->contribution->$field;
                    }
                }
            }

            // Second cutoff: loans
            if ($isSecondCutoff && $employee->contribution) {
                foreach (['sss_salary_loan', 'sss_calamity_loan', 'pagibig_salary_loan'] as $field) {
                    if (!empty($employee->contribution->$field)) {
                        $deductions[$field] = $employee->contribution->$field;
                        $totalDeductions += $employee->contribution->$field;
                    }
                }
            }

            // Manual deductions (always applied)
            foreach ($manualFields as $field) {
                $value = $existingPayroll->$field ?? 0;
                if ($value > 0) {
                    $deductions[$field] = $value;
                    $totalDeductions += $value;
                }
            }

            $netPay = $grossPay - $totalDeductions;

            // =====================
            // Prepare payroll data for saving
            // =====================
            $payrollData = [
                'employee_id' => $employee->id,
                'payroll_period_id' => $period->id,
                'days_worked' => $daysWorked,
                'days_absent' => $daysAbsent,
                'undertime_hours' => $undertimeHours,
                'undertime_deduction' => round($undertimeDeduction, 2),
                'overtime_hours' => $dtrs->sum('overtime_hours'),
                'sunday_ot_hours' => $dtrs->sum('sunday_ot_hours'),
                'night_diff_hours' => $dtrs->sum('night_diff_hours'),
                'night_diff_ot_hours' => $dtrs->sum('night_diff_ot_hours'),
                'daily_rate' => $dailyRate,
                'basic_salary' => round($basicSalaryAfterUndertime, 2),
                'overtime_salary' => round($overtimeSalary, 2),
                'sunday_ot_salary' => round($sundayOtSalary, 2),
                'night_diff_salary' => round($nightDiffSalary, 2),
                'night_diff_ot_salary' => round($nightDiffOtSalary, 2),
                'gross_pay' => round($grossPay, 2),
                'total_deductions' => round($totalDeductions, 2),
                'net_pay' => round($netPay, 2),
            ];

            // Add deduction fields dynamically
            foreach ($deductions as $key => $value) {
                $payrollData[$key] = $value;
            }

            Payroll::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'payroll_period_id' => $period->id,
                ],
                $payrollData
            );
        }

        $period->update(['status' => 'finalized']);
    }
}