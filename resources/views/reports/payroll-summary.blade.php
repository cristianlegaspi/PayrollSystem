<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Summary</title>
    <style>
        body { font-family: Arial, sans-serif; font-size:10px; }
        table { width:100%; border-collapse:collapse; }
        th, td { border:1px solid #000; padding:4px; }
        th { text-align:center; font-weight:bold; }
        td { text-align:right; }
        .text-left { text-align:left; }
        .bold { font-weight:bold; }
        .signature { height:40px; }
        .no-border td { border:none; }
        .signature-line { border-top:1px solid #000; width:200px; margin-top:40px; }
    </style>
</head>
<body>

<h3 style="text-align:center;margin-bottom:0;">E.A OCAMPO ENTERPRISES</h3>
Payroll Summary - {{ $period->description }} <br>
Branch: {{ $branch->branch_name }}

@php
$columns = [
    'days_worked', 'days_absent', 'undertime_hours', 'daily_rate', 'basic_salary',
    'overtime_salary', 'holiday_pay', 'gross_pay', 'cash_advance', 'shortages', 'other_deduction',
    'sss_er', 'sss_ee', 'premium_voluntary_ss_contribution', 'sss_salary_loan', 'sss_calamity_loan', 'philhealth_er', 'philhealth_ee',
    'pagibig_er', 'pagibig_ee', 'pagibig_salary_loan', 'total_deductions', 'net_pay'
];

$grand = array_fill_keys($columns, 0);
$admin = array_fill_keys($columns, 0);
$field = array_fill_keys($columns, 0);
@endphp

<table>
    <tr>
        <th class="text-left">Employee Name</th>
        <th>Days Worked</th>
        <th>Days Absent</th>
        <th>Undertime Hours</th>
        <th>Daily Rate</th>
        <th>Basic Salary</th>
        <th>Overtime</th>
        <th>Holiday Pay</th>
        <th>Gross Pay</th>
        <th>Cash Advance</th>
        <th>Shortages</th>
        <th>Other Deduction</th>
        <th>SSS ER</th>
        <th>SSS EE</th>
        <th>Premium SS Contribution</th>
        <th>SSS Salary Loan</th>
        <th>SSS Salary Calamity Loan</th>
        <th>PhilHealth ER</th>
        <th>PhilHealth EE</th>
        <th>Pagibig ER</th>
        <th>Pagibig EE</th>
        <th>Pagibig Salary Loan</th>
        <th>Total Deduction</th>
        <th>Net Pay</th>
        <th>Signature</th>
    </tr>

  @php
$startDay = \Carbon\Carbon::parse($period->start_date)->day;
$isFirstCutoff = $startDay >= 1 && $startDay <= 15;
$isSecondCutoff = $startDay >= 16;
@endphp

@php
$startDay = \Carbon\Carbon::parse($period->start_date)->day;
$isFirstCutoff = $startDay >= 1 && $startDay <= 15;
$isSecondCutoff = $startDay >= 16;
@endphp

@foreach($payrolls as $payroll)
@php
    $type = $payroll->employee->employee_type ?? 'Field';

    // ======================
    // Contributions / Loans based on cutoff
    // ======================
    $sss_ee = $isFirstCutoff ? ($payroll->contribution->sss_ee ?? 0) : 0;
    $philhealth_ee = $isFirstCutoff ? ($payroll->contribution->philhealth_ee ?? 0) : 0;
    $pagibig_ee = $isFirstCutoff ? ($payroll->contribution->pagibig_ee ?? 0) : 0;
    $premium_voluntary_ss_contribution = $isFirstCutoff ? ($payroll->contribution->premium_voluntary_ss_contribution ?? 0) : 0;

    $sss_er = $isFirstCutoff ? ($payroll->contribution->sss_er ?? 0) : 0;
    $philhealth_er = $isFirstCutoff ? ($payroll->contribution->philhealth_er ?? 0) : 0;
    $pagibig_er = $isFirstCutoff ? ($payroll->contribution->pagibig_er ?? 0) : 0;

    $sss_salary_loan = $isSecondCutoff ? ($payroll->contribution->sss_salary_loan ?? 0) : 0;
    $sss_calamity_loan = $isSecondCutoff ? ($payroll->contribution->sss_calamity_loan ?? 0) : 0;
    $pagibig_salary_loan = $isSecondCutoff ? ($payroll->contribution->pagibig_salary_loan ?? 0) : 0;

    // Manual deductions always included
    $cash_advance = $payroll->cash_advance ?? 0;
    $shortages = $payroll->shortages ?? 0;
    $other_deduction = $payroll->other_deduction ?? 0;

    // Total row deductions
    $row_total_deductions = $sss_ee + $philhealth_ee + $pagibig_ee + $premium_voluntary_ss_contribution
                            + $sss_salary_loan + $sss_calamity_loan + $pagibig_salary_loan
                            + $cash_advance + $shortages + $other_deduction;

    $totalOvertime = ($payroll->overtime_salary ?? 0)
                     + ($payroll->night_diff_salary ?? 0)
                     + ($payroll->sunday_ot_salary ?? 0)
                     + ($payroll->night_diff_ot_salary ?? 0);
@endphp

<tr>
    <td class="text-left">{{ $payroll->employee->full_name }}</td>
    <td>{{ $payroll->days_worked }}</td>
    <td>{{ $payroll->days_absent }}</td>
    <td>{{ number_format($payroll->undertime_hours,2) }}</td>
    <td>{{ number_format($payroll->daily_rate,2) }}</td>
    <td>{{ number_format($payroll->basic_salary,2) }}</td>
    <td>{{ number_format($totalOvertime,2) }}</td>
    <td>{{ number_format($payroll->holiday_pay ?? 0,2) }}</td>
    <td>{{ number_format($payroll->gross_pay,2) }}</td>
    <td>{{ number_format($cash_advance,2) }}</td>
    <td>{{ number_format($shortages,2) }}</td>
    <td>{{ number_format($other_deduction,2) }}</td>
    <td>{{ number_format($sss_er,2) }}</td>
    <td>{{ number_format($sss_ee,2) }}</td>
    <td>{{ number_format($premium_voluntary_ss_contribution,2) }}</td>
    <td>{{ number_format($sss_salary_loan,2) }}</td>
    <td>{{ number_format($sss_calamity_loan,2) }}</td>
    <td>{{ number_format($philhealth_er,2) }}</td>
    <td>{{ number_format($philhealth_ee,2) }}</td>
    <td>{{ number_format($pagibig_er,2) }}</td>
    <td>{{ number_format($pagibig_ee,2) }}</td>
    <td>{{ number_format($pagibig_salary_loan,2) }}</td>
    <td class="bold">{{ number_format($row_total_deductions,2) }}</td>
    <td class="bold">{{ number_format($payroll->gross_pay - $row_total_deductions,2) }}</td>
    <td class="signature"></td>
</tr>
@endforeach

    {{-- TOTAL ADMIN --}}
    <tr class="bold">
        <td class="text-left">TOTAL ADMIN</td>
        @foreach($columns as $col)
            <td>{{ number_format($admin[$col],2) }}</td>
        @endforeach
        <td></td>
    </tr>

    {{-- TOTAL FIELD --}}
    <tr class="bold">
        <td class="text-left">TOTAL FIELD</td>
        @foreach($columns as $col)
            <td>{{ number_format($field[$col],2) }}</td>
        @endforeach
        <td></td>
    </tr>

    {{-- TOTAL COMPANY --}}
    <tr class="bold">
        <td class="text-left">TOTAL COMPANY</td>
        @foreach($columns as $col)
            <td>{{ number_format($grand[$col],2) }}</td>
        @endforeach
        <td></td>
    </tr>
</table>

<br><br><br>

<table class="no-border">
    <tr>
        <td class="text-left">
            <div class="signature-line"></div>
            Prepared by:<br>Name and Signature
        </td>
        <td></td>
        <td class="text-left">
            <div class="signature-line"></div>
            Approved by:<br>
            <strong>EDUARDO A. OCAMPO</strong><br>
            Authorized Signatory
        </td>
    </tr>
</table>



</body>
</html>