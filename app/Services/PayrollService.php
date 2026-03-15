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
        // Get employees with DTR in the period
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

            // =========================
            // ATTENDANCE SUMMARY
            // =========================

            // Only count actual worked days
            $daysWorked = $dtrs->filter(function ($dtr) {
                return $dtr->status === 'on_duty' && $dtr->sunday_ot_hours == 0;
            })->count();

            $daysAbsent = $dtrs->where('status', 'absent_without_pay')->count();

            // Undertime only if actually on duty
            $undertimeHours = $dtrs
                ->filter(fn($dtr) => $dtr->status === 'on_duty')
                ->sum('undertime_hours');

            // =========================
            // RATE COMPUTATION
            // =========================

            $dailyRate = (float) $employee->daily_rate;
            $hourlyRate = $dailyRate / 8;

            // =========================
            // BASIC SALARY
            // =========================

            $basicSalary = $dailyRate * $daysWorked;

            // Deduct undertime
            $undertimeDeduction = $undertimeHours * $hourlyRate;

            $basicSalaryAfterUndertime = $basicSalary - $undertimeDeduction;

            // =========================
            // OT & NIGHT DIFFERENTIAL
            // =========================

            $overtimeSalary = 0;
            $sundayOtSalary = 0;
            $nightDiffSalary = 0;
            $nightDiffOtSalary = 0;

            foreach ($dtrs as $dtr) {

                // Night Differential (10%)
                $nightDiffSalary += $dtr->night_diff_hours * ($hourlyRate * 0.10);

                // Regular OT (125%)
                $overtimeSalary += $dtr->overtime_hours * ($hourlyRate * 1.25);

                // Sunday OT (130%)
                $sundayOtSalary += $dtr->sunday_ot_hours * ($hourlyRate * 1.30);

                // Night Differential OT
                $nightDiffOtSalary += $dtr->night_diff_ot_hours * (($hourlyRate * 1.25) * 0.10);
            }

            // =========================
            // GROSS PAY
            // =========================

            $grossPay =
                $basicSalaryAfterUndertime +
                $overtimeSalary +
                $sundayOtSalary +
                $nightDiffSalary +
                $nightDiffOtSalary;

            // =========================
            // GOVERNMENT DEDUCTIONS
            // =========================

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

            // =========================
            // PRESERVE MANUAL ENTRIES
            // =========================

            $existingPayroll = Payroll::where('employee_id', $employee->id)
                ->where('payroll_period_id', $period->id)
                ->first();

            $cashAdvance = $existingPayroll->cash_advance ?? 0;
            $shortages = $existingPayroll->shortages ?? 0;

            $totalDeductionsWithManual =
                $totalDeductions +
                $cashAdvance +
                $shortages;

            // =========================
            // NET PAY
            // =========================

            $netPay = $grossPay - $totalDeductionsWithManual;

            // =========================
            // SAVE PAYROLL
            // =========================

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

        // =========================
        // FINALIZE PAYROLL PERIOD
        // =========================

        $period->update([
            'status' => 'finalized'
        ]);
    }
}