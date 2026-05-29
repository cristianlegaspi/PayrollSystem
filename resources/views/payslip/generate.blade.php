The error happens because the code variable declaration block was split. The variable `$sss_ee` was defined *after* the cutoff conditional statements used it.

Here is the fully fixed, integrated, and clean **Blade template** file. Replacing your entire file with this will resolve the `Undefined variable` error and accurately separate the Regular Days and Legal Holiday Credit.

```html
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

<body>

<div class="header">
    <h1>E.A OCAMPO ENTERPRISES</h1>
    <p>PAYROLL PERIOD: {{ $data['period'] }}</p>
</div>

<table class="no-border">
    <tr>
        <td><strong>Name:</strong> {{ $data['employee_name'] }}</td>
        <td><strong>Daily Rate:</strong> PHP {{ number_format($data['daily_rate'], 2) }}</td>
    </tr>
    <tr>
        <td><strong>Position:</strong> {{ $data['position'] }}</td>
        <td><strong>Date:</strong> {{ $data['date_generated'] }}</td>
    </tr>
</table>

@php
    // 1. Determine cutoff from period string like "March 16-31, 2026"
    preg_match('/(\d+)-(\d+)/', $data['period'], $matches);
    $startDay = $matches[1] ?? 1;
    $startDay = (int) $startDay;

    $isFirstCutoff = $startDay >= 1 && $startDay <= 15;
    $isSecondCutoff = $startDay >= 16;

    // 2. Conditional contributions
    $sss_ee = $isFirstCutoff ? ($data['sss_ee'] ?? 0) : 0;
    $philhealth_ee = $isFirstCutoff ? ($data['philhealth_ee'] ?? 0) : 0;
    $pagibig_ee = $isFirstCutoff ? ($data['pagibig_ee'] ?? 0) : 0;
    $premium_ss = $isFirstCutoff ? ($data['premium_voluntary_ss_contribution'] ?? 0) : 0;

    $sss_salary_loan = $isSecondCutoff ? ($data['sss_salary_loan'] ?? 0) : 0;
    $sss_calamity_loan = $isSecondCutoff ? ($data['sss_calamity_loan'] ?? 0) : 0;
    $pagibig_salary_loan = $isSecondCutoff ? ($data['pagibig_salary_loan'] ?? 0) : 0;

    // 3. Other base values
    $cash_advance = $data['cash_advance'] ?? 0;
    $shortages = $data['shortages'] ?? 0;
    $other_deduction = $data['other_deduction'] ?? 0;
    $other_incentives = $data['other_incentives'] ?? 0;

    // 4. Calculations for totals
    $total_deductions = $sss_ee + $philhealth_ee + $pagibig_ee + $premium_ss
                        + $sss_salary_loan + $sss_calamity_loan + $pagibig_salary_loan
                        + $cash_advance + $shortages + $other_deduction;

    $final_gross_pay = ($data['gross_pay'] ?? 0) + $other_incentives;
    $final_net_pay = $final_gross_pay - $total_deductions;

    // 5. Fixed Holiday Breakdown Logic
    $dailyRate = $data['daily_rate'] ?? 0;
    $basicSalary = $data['basic_salary'] ?? 0;
    $rawDaysWorked = $data['days_worked'] ?? 0; 

    $calculatedDaysWorked = $rawDaysWorked;
    $legalHolidayDays = 0;

    if ($dailyRate > 0) {
        $totalPaidUnits = (int)round($basicSalary / $dailyRate); 
        
        if ($totalPaidUnits > $rawDaysWorked && $rawDaysWorked > 0) {
            $legalHolidayDays = $totalPaidUnits - $rawDaysWorked; 
            $calculatedDaysWorked = $rawDaysWorked - $legalHolidayDays;
        } else {
            $legalHolidayDays = 0;
            $calculatedDaysWorked = $totalPaidUnits;
        }
    }
@endphp

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
                + {{ $legalHolidayDays }} Legal Holiday Credit
            @endif
            )
        </td>
        <td class="right">PHP {{ number_format($basicSalary, 2) }}</td>
    </tr>

    <tr>
        <td>Undertime Deduction ({{ number_format(($data['daily_rate'] ?? 0) / 8, 2) }} × {{ $data['undertime_hours'] ?? 0 }} hrs)</td>
        <td class="right">PHP {{ number_format($data['undertime_deduction'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Regular Overtime Pay</td>
        <td class="right">PHP {{ number_format($data['overtime_salary'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Night Differential Pay</td>
        <td class="right">PHP {{ number_format($data['night_diff_salary'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Night Differential OT Pay</td>
        <td class="right">PHP {{ number_format($data['night_diff_ot_salary'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Rest Day OT Pay</td>
        <td class="right">PHP {{ number_format($data['rest_day_ot_salary'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Sunday OT Pay</td>
        <td class="right">PHP {{ number_format($data['sunday_ot_salary'] ?? 0, 2) }}</td>
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
            Employer Signature
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
