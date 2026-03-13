<?php

namespace App\Services;

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipService
{
    public static function generate(Payroll $payroll)
    {
        $employee = $payroll->employee;
        $contribution = $employee->contribution;

        // 1. Prepare the data array
        $data = [
            'company'         => 'FULLTANK GAS STATION',
            'payroll_period'  => $payroll->payrollPeriod->description,
            'employee_name'   => $employee->full_name,
            'position'        => $employee->position->position_name ?? 'N/A',
            'date'            => now()->format('M d, Y'),
            'daily_rate'      => $employee->daily_rate,
            'days_worked'     => $payroll->days_worked,
            'days_absent'     => $payroll->days_absent,
            'undertime_hours' => $payroll->undertime_hours,
            'basic_salary'    => $payroll->basic_salary,
            'additions'       => [
                'holiday_ot' => $payroll->holiday_ot ?? 0,
                'other'      => $payroll->other_additions ?? 0,
            ],
            'deductions'      => [
                'sss'        => $contribution->sss_ee ?? 0,
                'philhealth' => $contribution->philhealth_ee ?? 0,
                'pagibig'    => $contribution->pagibig_ee ?? 0,
                'loan'       => ($contribution->sss_salary_loan ?? 0) 
                              + ($contribution->sss_calamity_loan ?? 0) 
                              + ($contribution->pagibig_loan ?? 0) 
                              + ($contribution->pagibig_salary_loan ?? 0),
                'shortages'  => $payroll->shortages ?? 0,
                'advances'   => $payroll->cash_advance ?? 0,
            ],
        ];

        // 2. Load the view with the data
      return Pdf::loadView('payslip.generate', compact('data'))
          ->stream("Payslip.pdf");
    }
}