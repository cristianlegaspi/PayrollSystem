<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Payslip</title>
<style>
@page { size: A4; margin: 15px; }
body { font-family: Arial, sans-serif; font-size: 9px; margin: 0; color: #333; }
table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
td, th { border: 1px solid #ccc; padding: 4px 6px; }
th { background-color: #f2f2f2; font-weight: bold; }
.section { background: #e0e0e0; font-weight: bold; text-transform: uppercase; }
.right { text-align: right; }
.center { text-align: center; }
.bold { font-weight: bold; }
.no-border td { border: none; padding: 2px 0; }
.net { font-size: 11px; font-weight: bold; background: #007bff; color: #fff; padding: 6px; }
.header { text-align: center; margin-bottom: 10px; }
.header h1 { margin: 0; font-size: 16px; letter-spacing: 1px; color: #007bff; }
.header p { margin: 1px 0 0; font-size: 10px; }
.earnings-table tr:nth-child(even), .deductions-table tr:nth-child(even) { background-color: #f9f9f9; }
.signature { margin-top: 20px; }
.signature td { padding-top: 40px; }
.footer { text-align: center; font-size: 8px; color: #777; margin-top: 10px; }
</style>
</head>

@php
    // Fallback normalization in case $payroll or $data is structured differently by the controller
    $payrollObj = $payroll ?? null;
    $contribution = isset($payrollObj) ? $payrollObj->contribution : (object)[];
    $employee = isset($payrollObj) ? $payrollObj->employee : (object)[];
    $periodObj = $period ?? null;

    // 1. Safe extraction of explicit legal holiday records
    $legalHolidaysCount = 0;
    if (isset($employee->id) && isset($periodObj->start_date)) {
        $legalHolidaysCount = \App\Models\DailyTimeRecord::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$periodObj->start_date, $periodObj->end_date])
            ->where(function($query) {
                $query->where('remarks', 'LIKE', '%Legal Holiday%')
                      ->orWhere('status', 'LIKE', '%legal_holiday%');
            })
            ->count();
    }

    // 2. Safe merge of existing arrays/objects into a normalized workspace array
    $payrollData = [
        'period' => $data['period'] ?? $periodObj->description ?? '',
        'employee_name' => $data['employee_name'] ?? $employee->full_name ?? '',
        'daily_rate' => (float) ($data['daily_rate'] ?? $payrollObj->daily_rate ?? 0),
        'position' => $data['position'] ?? $employee->position->position_name ?? '',
        'date_generated' => $data['date_generated'] ?? now()->format('F d, Y'),
        'days_worked' => (int) ($data['days_worked'] ?? $payrollObj->days_worked ?? 0),
        'basic_salary' => (float) ($data['basic_salary'] ?? $payrollObj->basic_salary ?? 0),
        'gross_pay' => (float) ($data['gross_pay'] ?? $payrollObj->gross_pay ?? 0),
        'undertime_hours' => (float) ($data['undertime_hours'] ?? $payrollObj->undertime_hours ?? 0),
        'undertime_deduction' => (float) ($data['undertime_deduction'] ?? $payrollObj->undertime_deduction ?? 0),
        'overtime_salary' => (float) ($data['overtime_salary'] ?? $payrollObj->overtime_salary ?? 0),
        'night_diff_salary' => (float) ($data['night_diff_salary'] ?? $payrollObj->night_diff_salary ?? 0),
        'night_diff_ot_salary' => (float) ($data['night_diff_ot_salary'] ?? $payrollObj->night_diff_ot_salary ?? 0),
        'rest_day_ot_salary' => (float) ($data['rest_day_ot_salary'] ?? $payrollObj->rest_day_ot_salary ?? 0),
        'sunday_ot_salary' => (float) ($data['sunday_ot_salary'] ?? $payrollObj->sunday_ot_salary ?? 0),
        'sss_ee' => (float) ($data['sss_ee'] ?? $contribution->sss_ee ?? 0),
        'philhealth_ee' => (float) ($data['philhealth_ee'] ?? $contribution->philhealth_ee ?? 0),
        'pagibig_ee' => (float) ($data['pagibig_ee'] ?? $contribution->pagibig_ee ?? 0),
        'premium_voluntary_ss_contribution' => (float) ($data['premium_voluntary_ss_contribution'] ?? $contribution->premium_voluntary_ss_contribution ?? 0),
        'sss_salary_loan' => (float) ($data['sss_salary_loan'] ?? $contribution->sss_salary_loan ?? 0),
        'sss_calamity_loan' => (float) ($data['sss_calamity_loan'] ?? $contribution->sss_calamity_loan ?? 0),
        'pagibig_salary_loan' => (float) ($data['pagibig_salary_loan'] ?? $contribution->pagibig_salary_loan ?? 0),
        'cash_advance' => (float) ($data['cash_advance'] ?? $payrollObj->cash_advance ?? 0),
        'shortages' => (float) ($data['shortages'] ?? $payrollObj->shortages ?? 0),
        'other_deduction' => (float) ($data['other_deduction'] ?? $payrollObj->other_deduction ?? 0),
        'other_incentives' => (float) ($data['other_incentives'] ?? $payrollObj->other_incentives ?? 0),
    ];

    preg_match('/(\d+)-(\d+)/', $payrollData['period'], $matches);
    $startDay = (int) ($matches[1] ?? 1);

    $isFirstCutoff = $startDay >= 1 && $startDay <= 15;
    $isSecondCutoff = $startDay >= 16;

    $sss_ee = $isFirstCutoff ? $payrollData['sss_ee'] : 0;
    $philhealth_ee = $isFirstCutoff ? $payrollData['philhealth_ee'] : 0;
    $pagibig_ee = $isFirstCutoff ? $payrollData['pagibig_ee'] : 0;
    $premium_ss = $isFirstCutoff ? $payrollData['premium_voluntary_ss_contribution'] : 0;

    $sss_salary_loan = $isSecondCutoff ? $payrollData['sss_salary_loan'] : 0;
    $sss_calamity_loan = $isSecondCutoff ? $payrollData['sss_calamity_loan'] : 0;
    $pagibig_salary_loan = $isSecondCutoff ? $payrollData['pagibig_salary_loan'] : 0;

    $cash_advance = $payrollData['cash_advance'];
    $shortages = $payrollData['shortages'];
    $other_deduction = $payrollData['other_deduction'];
    $other_incentives = $payrollData['other_incentives'];

    $total_deductions = $sss_ee + $philhealth_ee + $pagibig_ee + $premium_ss + 
                        $sss_salary_loan + $sss_calamity_loan + $pagibig_salary_loan + 
                        $cash_advance + $shortages + $other_deduction;

    $final_gross_pay = $payrollData['gross_pay'] + $other_incentives;
    $final_net_pay = $final_gross_pay - $total_deductions;

    // Split Paid units to clean out Regular Days vs Legal Holiday credits
    $dailyRate = $payrollData['daily_rate'];
    $basicSalary = $payrollData['basic_salary'];
    
    $totalPaidUnits = $dailyRate > 0 ? (int)round($basicSalary / $dailyRate) : 0;
    
    if ($legalHolidaysCount > 0 && $totalPaidUnits >= $legalHolidaysCount) {
        $legalHolidayDays = $legalHolidaysCount;
        $calculatedDaysWorked = $totalPaidUnits - $legalHolidayDays;
    } else {
        $legalHolidayDays = ($totalPaidUnits == 15 && $payrollData['days_worked'] == 14) ? 1 : 0; 
        $calculatedDaysWorked = $totalPaidUnits - $legalHolidayDays;
    }
@endphp

<body>

<div class="header">
    <h1>E.A OCAMPO ENTERPRISES</h1>
    <p>PAYROLL PERIOD: {{ $payrollData['period'] }}</p>
</div>

<table class="no-border">
    <tr>
        <td><strong>Name:</strong> {{ $payrollData['employee_name'] }}</td>
        <td><strong>Daily Rate:</strong> PHP {{ number_format($payrollData['daily_rate'], 2) }}</td>
    </tr>
    <tr>
        <td><strong>Position:</strong> {{ $payrollData['position'] }}</td>
        <td><strong>Date:</strong> {{ $payrollData['date_generated'] }}</td>
    </tr>
</table>

<table class="earnings-table">
    <tr class="section">
        <td>Description</td>
        <td class="right">Amount</td>
    </tr>

    <tr>
        <td>
            Basic Salary (
            {{ $calculatedDaysWorked }} Regular Day{{ $calculatedDaysWorked != 1 ? 's' : '' }}
            @if($legalHolidayDays > 0)
                + {{ $legalHolidayDays }} Legal Holiday Credit{{ $legalHolidayDays != 1 ? 's' : '' }} Credited
            @endif
            )
        </td>
        <td class="right">PHP {{ number_format($basicSalary, 2) }}</td>
    </tr>

    <tr>
        <td>Undertime Deduction (PHP {{ number_format(($payrollData['daily_rate'] ?? 0) / 8, 2) }} × {{ $payrollData['undertime_hours'] ?? 0 }} hrs)</td>
        <td class="right">PHP {{ number_format($payrollData['undertime_deduction'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Regular Overtime Pay</td>
        <td class="right">PHP {{ number_format($payrollData['overtime_salary'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Night Differential Pay</td>
        <td class="right">PHP {{ number_format($payrollData['night_diff_salary'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Night Differential OT Pay</td>
        <td class="right">PHP {{ number_format($payrollData['night_diff_ot_salary'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Rest Day OT Pay</td>
        <td class="right">PHP {{ number_format($payrollData['rest_day_ot_salary'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Sunday OT Pay</td>
        <td class="right">PHP {{ number_format($payrollData['sunday_ot_salary'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Other Incentives</td>
        <td class="right">PHP {{ number_format($other_incentives, 2) }}</td>
    </tr>

    <tr class="bold">
        <td>GROSS PAY</td>
        <td class="right">PHP {{ number_format($final_gross_pay, 2) }}</td>
    </tr>
</table>

<table class="deductions-table">
    <tr class="section">
        <td colspan="2">DEDUCTIONS</td>
    </tr>

    <tr>
        <td>SSS (EE Share)</td>
        <td class="right">PHP {{ number_format($sss_ee, 2) }}</td>
    </tr>

    <tr>
        <td>PhilHealth (EE Share)</td>
        <td class="right">PHP {{ number_format($philhealth_ee, 2) }}</td>
    </tr>

    <tr>
        <td>Pag-IBIG (EE Share)</td>
        <td class="right">PHP {{ number_format($pagibig_ee, 2) }}</td>
    </tr>

    <tr>
        <td>SSS Premium Contribution</td>
        <td class="right">PHP {{ number_format($premium_ss, 2) }}</td>
    </tr>

    <tr>
        <td>SSS Salary Loan</td>
        <td class="right">PHP {{ number_format($sss_salary_loan, 2) }}</td>
    </tr>

    <tr>
        <td>SSS Calamity Loan</td>
        <td class="right">PHP {{ number_format($sss_calamity_loan, 2) }}</td>
    </tr>

    <tr>
        <td>Pag-IBIG Loan</td>
        <td class="right">PHP {{ number_format($pagibig_salary_loan, 2) }}</td>
    </tr>

    <tr>
        <td>Cash Advance</td>
        <td class="right">PHP {{ number_format($cash_advance, 2) }}</td>
    </tr>

    <tr>
        <td>Shortages</td>
        <td class="right">PHP {{ number_format($shortages, 2) }}</td>
    </tr>

    <tr>
        <td>Other Deduction</td>
        <td class="right">PHP {{ number_format($other_deduction, 2) }}</td>
    </tr>

    <tr class="bold">
        <td>TOTAL DEDUCTIONS</td>
        <td class="right">PHP {{ number_format($total_deductions, 2) }}</td>
    </tr>
</table>

<table>
    <tr class="net">
        <td>NET PAY</td>
        <td class="right">PHP {{ number_format($final_net_pay, 2) }}</td>
    </tr>
</table>

<table class="no-border signature">
    <tr>
        <td class="center">
            _________________________<br>
            Authorized Signature
        </td>
        <td class="center">
            _________________________<br>
            Employee Signature
        </td>
    </tr>
</table>

<div class="footer">
    This payslip is system generated.
</div>

</body>
</html>