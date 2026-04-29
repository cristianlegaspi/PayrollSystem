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
        $employeeIds = DailyTimeRecord::whereBetween('work_date', [$period->start_date, $period->end_date])
            ->pluck('employee_id')
            ->unique();

        $employees = Employee::with('contribution')
            ->whereIn('id', $employeeIds)
            ->get();

        foreach ($employees as $employee) {
            $dtrs = DailyTimeRecord::where('employee_id', $employee->id)
                ->whereBetween('work_date', [$period->start_date, $period->end_date])
                ->orderBy('work_date')
                ->get();

            $dailyRate = (float) ($employee->daily_rate ?? 0);
            $hourlyRate = $dailyRate / 8;

            // Counters
            $daysWorked = 0;
            $daysAbsent = 0;
            $totalUndertimeHours = 0;

            // Salary buckets
            $totalRegularSalary = 0;
            $totalOvertimeSalary = 0;
            $totalSundaySalary = 0;
            $totalRestDaySalary = 0;
            $totalNightDiffSalary = 0;
            $totalNightDiffOtSalary = 0;

            foreach ($dtrs as $dtr) {
                $status = strtolower(trim((string) $dtr->status));
                $remarks = strtolower(trim((string) $dtr->remarks));

                $otHrs = (float) ($dtr->overtime_hours ?? 0);
                $rdOtHrs = (float) ($dtr->rest_day_ot_hours ?? 0);
                $sunOtHrs = (float) ($dtr->sunday_ot_hours ?? 0);
                $ndHrs = (float) ($dtr->night_diff_hours ?? 0);
                $ndOtHrs = (float) ($dtr->night_diff_ot_hours ?? 0);
                $utHrs = (float) ($dtr->undertime_hours ?? 0);
                $totalHrs = (float) ($dtr->total_hours ?? 0);

                $totalUndertimeHours += $utHrs;

                /*
                 |--------------------------------------------------------------
                 | 1. REST DAY
                 | Form logic puts worked rest day hours into rest_day_ot_hours,
                 | not total_hours. So compute using rdOtHrs.
                 |--------------------------------------------------------------
                 */
                if ($status === 'rest_day') {
                    if ($rdOtHrs > 0) {
                        $totalRestDaySalary += ($rdOtHrs * ($hourlyRate * 1.30));
                        $daysWorked += 1;
                    }

                    // Add-ons can still apply below.
                }

                /*
                 |--------------------------------------------------------------
                 | 2. LEGAL HOLIDAY
                 | legal_holiday + worked hours = double pay
                 | legal_holiday + no work = regular pay only
                 |--------------------------------------------------------------
                 */
                elseif ($status === 'legal_holiday') {
                    if ($totalHrs > 0) {
                        $totalRegularSalary += ($dailyRate * 2);

                        if ($otHrs > 0) {
                            $totalOvertimeSalary += ($otHrs * ($hourlyRate * 2.60));
                        }

                        $daysWorked += 1;
                    } else {
                        // Legal Holiday (No Work) = regular daily pay only
                        $totalRegularSalary += $dailyRate;
                    }
                }

                /*
                 |--------------------------------------------------------------
                 | 3. SPECIAL HOLIDAY
                 | worked = 130%
                 | no work = no pay
                 |--------------------------------------------------------------
                 */
                elseif ($status === 'special_holiday') {
                    if ($totalHrs > 0) {
                        $totalRegularSalary += ($dailyRate * 1.30);

                        if ($otHrs > 0) {
                            $totalOvertimeSalary += ($otHrs * ($hourlyRate * 1.69));
                        }

                        $daysWorked += 1;
                    }
                }

                /*
                 |--------------------------------------------------------------
                 | 4. ABSENT WITH PAY
                 |--------------------------------------------------------------
                 */
                elseif ($status === 'absent_with_pay') {
                    $totalRegularSalary += $dailyRate;
                }

                /*
                 |--------------------------------------------------------------
                 | 5. ABSENT WITHOUT PAY
                 |--------------------------------------------------------------
                 */
                elseif ($status === 'absent_without_pay') {
                    $daysAbsent += 1;
                }

                /*
                 |--------------------------------------------------------------
                 | 6. NORMAL DAY / NIGHT SHIFT / OTHER WORKED DAY
                 | DTR form stores normal worked hours in total_hours and status
                 | values like on_duty and night_shift. :contentReference[oaicite:2]{index=2}
                 |--------------------------------------------------------------
                 */
                elseif ($status === 'on_duty' || $status === 'night_shift') {
                    if ($totalHrs > 0) {
                        $totalRegularSalary += $dailyRate;

                        if ($otHrs > 0) {
                            $totalOvertimeSalary += ($otHrs * ($hourlyRate * 1.25));
                        }

                        $daysWorked += 1;
                    }
                }

                /*
                 |--------------------------------------------------------------
                 | 7. Fallback for worked entries with hours
                 |--------------------------------------------------------------
                 */
                elseif ($totalHrs > 0) {
                    $totalRegularSalary += $dailyRate;

                    if ($otHrs > 0) {
                        $totalOvertimeSalary += ($otHrs * ($hourlyRate * 1.25));
                    }

                    $daysWorked += 1;
                }

                /*
                 |--------------------------------------------------------------
                 | ADD-ONS
                 |--------------------------------------------------------------
                 */
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

            // Final computation
            $undertimeDeduction = $totalUndertimeHours * $hourlyRate;

            $grossPay = (
                $totalRegularSalary +
                $totalOvertimeSalary +
                $totalSundaySalary +
                $totalRestDaySalary +
                $totalNightDiffSalary +
                $totalNightDiffOtSalary
            ) - $undertimeDeduction;

            // Deductions
            $totalDeductions = 0;

            if ($employee->contribution) {
                $startDay = Carbon::parse($period->start_date)->day;

                if ($startDay <= 15) {
                    $totalDeductions += (float) ($employee->contribution->sss_ee ?? 0);
                    $totalDeductions += (float) ($employee->contribution->philhealth_ee ?? 0);
                    $totalDeductions += (float) ($employee->contribution->pagibig_ee ?? 0);
                    $totalDeductions += (float) ($employee->contribution->premium_voluntary_ss_contribution ?? 0);
                } else {
                    $totalDeductions += (float) ($employee->contribution->sss_salary_loan ?? 0);
                    $totalDeductions += (float) ($employee->contribution->pagibig_salary_loan ?? 0);
                    $totalDeductions += (float) ($employee->contribution->sss_calamity_loan ?? 0);
                }
            }

            Payroll::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'payroll_period_id' => $period->id,
                ],
                [
                    'days_worked'           => $daysWorked,
                    'days_absent'           => $daysAbsent,
                    'undertime_hours'       => round($totalUndertimeHours, 2),
                    'overtime_hours'        => round((float) $dtrs->sum('overtime_hours'), 2),
                    'night_diff_hours'      => round((float) $dtrs->sum('night_diff_hours'), 2),
                    'night_diff_ot_hours'   => round((float) $dtrs->sum('night_diff_ot_hours'), 2),
                    'daily_rate'            => round($dailyRate, 2),
                    'basic_salary'          => round($totalRegularSalary, 2),
                    'overtime_salary'       => round($totalOvertimeSalary, 2),
                    'night_diff_salary'     => round($totalNightDiffSalary, 2),
                    'night_diff_ot_salary'  => round($totalNightDiffOtSalary, 2),
                    'gross_pay'             => round($grossPay, 2),
                    'total_deductions'      => round($totalDeductions, 2),
                    'net_pay'               => round($grossPay - $totalDeductions, 2),
                    'sunday_ot_hours'       => round((float) $dtrs->sum('sunday_ot_hours'), 2),
                    'sunday_ot_salary'      => round($totalSundaySalary, 2),
                    'undertime_deduction'   => round($undertimeDeduction, 2),
                    'rest_day_ot_hours'     => round((float) $dtrs->sum('rest_day_ot_hours'), 2),
                    'rest_day_ot_salary'    => round($totalRestDaySalary, 2),
                ]
            );
        }

        $period->update([
            'status' => 'Finalized',
        ]);
    }
}