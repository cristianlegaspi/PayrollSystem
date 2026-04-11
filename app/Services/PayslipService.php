<?php

namespace App\Services;

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipService
{
    public static function generate(Payroll $payroll)
    {
        $employee = $payroll->employee;
        $contribution = $payroll->contribution;

        $period = $payroll->payrollPeriod;

       $period = $payroll->payrollPeriod;

        // We fetch the counts by looking at the DailyTimeRecord model
        // We use trim and lowercase to ensure we find the match even if there are spaces
        $legalHolidays = \App\Models\DailyTimeRecord::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$period->start_date, $period->end_date])
            ->where('remarks', 'LIKE', '%Legal Holiday%')
            ->count();

        $specialHolidays = \App\Models\DailyTimeRecord::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$period->start_date, $period->end_date])
            ->where('remarks', 'LIKE', '%Special Holiday%')
            ->count();





        $data = [
            'company' => 'FULLTANK GAS STATION',
            'period' => $payroll->payrollPeriod->description,

            'employee_name' => $employee->full_name,
            'position' => $employee->position->position_name ?? 'Pump Attendant',

            'daily_rate' => $payroll->daily_rate,
            'days_worked' => $payroll->days_worked,

            // Pass the calculated counts here
            'legal_holidays' => $legalHolidays,
            'special_holidays' => $specialHolidays,




            'days_absent' => $payroll->days_absent,
            'undertime_hours' => $payroll->undertime_hours,
            'undertime_deduction' => $payroll->undertime_deduction,

            // EARNINGS
            'basic_salary' => $payroll->basic_salary,
            'overtime_salary' => $payroll->overtime_salary,
            'night_diff_salary' => $payroll->night_diff_salary,
            'night_diff_ot_salary' => $payroll->night_diff_ot_salary,
            'rest_day_ot_salary' => $payroll->rest_day_ot_salary,
            'sunday_ot_salary' => $payroll->sunday_ot_salary,
            'gross_pay' => $payroll->gross_pay,

            // DEDUCTIONS
            'sss_ee' => $contribution->sss_ee ?? 0,
            'philhealth_ee' => $contribution->philhealth_ee ?? 0,
            'pagibig_ee' => $contribution->pagibig_ee ?? 0,
            'sss_salary_loan' => $contribution->sss_salary_loan ?? 0,
            'premium_voluntary_ss_contribution' => $contribution->premium_voluntary_ss_contribution ?? 0,
            'sss_calamity_loan' => $contribution->sss_calamity_loan ?? 0,
            'pagibig_salary_loan' => $contribution->pagibig_salary_loan ?? 0,
            'cash_advance' => $payroll->cash_advance ?? 0,
            'shortages' => $payroll->shortages ?? 0,
            'other_deduction' => $payroll->other_deduction ?? 0,

            'total_deductions' => $payroll->total_deductions +
                                  ($payroll->cash_advance ?? 0) +
                                 ($payroll->other_deduction ?? 0) +

                                  ($payroll->shortages ?? 0),

            'net_pay' => $payroll->net_pay,

            'date_generated' => now()->format('M d, Y'),
        ];

        return Pdf::loadView('payslip.generate', compact('data'))
            ->setPaper('a5', 'portrait')
            ->stream("Payslip-{$employee->full_name}.pdf");
    }
}