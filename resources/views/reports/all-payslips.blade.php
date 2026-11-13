<!DOCTYPE html>

<html>

<head>

<meta charset="utf-8">

<title>All Payslips</title>

<style>

@page { size: A4; margin: 15px; }

body {

    font-family: Arial, sans-serif;

    font-size: 9px;

    margin: 0;

    color: #333;

}

table {

    width: 100%;

    border-collapse: collapse;

    margin-bottom: 6px;

}

td, th {

    border: 1px solid #ccc;

    padding: 4px 6px;

}

th {

    background-color: #f2f2f2;

    font-weight: bold;

}

.section {

    background: #e0e0e0;

    font-weight: bold;

    text-transform: uppercase;

}

.right { text-align: right; }

.center { text-align: center; }

.bold { font-weight: bold; }

.no-border td {

    border: none;

    padding: 2px 0;

}

.net {

    font-size: 11px;

    font-weight: bold;

    background: #007bff;

    color: #fff;

    padding: 6px;

}

.header {

    text-align: center;

    margin-bottom: 10px;

}

.header h1 {

    margin: 0;

    font-size: 16px;

    letter-spacing: 1px;

    color: #007bff;

}

.header p {

    margin: 1px 0 0;

    font-size: 10px;

}

.earnings-table tr:nth-child(even),

.deductions-table tr:nth-child(even) {

    background-color: #f9f9f9;

}

.signature {

    margin-top: 20px;

}

.signature td {

    padding-top: 40px;

}

.footer {

    text-align: center;

    font-size: 8px;

    color: #777;

    margin-top: 10px;

}

.payslip-page {

    page-break-after: always;

}

.payslip-page:last-child {

    page-break-after: avoid;

}

</style>

</head>

<body>

@php
    // Special holiday premium rate (30% of the daily rate).
    // Change this value if the special holiday rate rule changes.
    $specialHolidayRate = 0.30;
@endphp

@foreach($payrolls as $payroll)

@php

    $contribution = $payroll->contribution;

    $employeeId = $payroll->employee->id ?? null;

    $legalHolidaysCount = 0;

    $specialHolidaysCount = 0;

    if ($employeeId) {

        // Legal holiday days recorded in the DTR.
        $legalHolidaysCount = \App\Models\DailyTimeRecord::where('employee_id', $employeeId)

            ->whereBetween('work_date', [$period->start_date, $period->end_date])

            ->where(function($query) {

                $query->where('remarks', 'LIKE', '%Legal Holiday%')

                      ->orWhere('status', 'LIKE', '%legal_holiday%');

            })

            ->count();

        // Special holiday days recorded in the DTR.
        $specialHolidaysCount = \App\Models\DailyTimeRecord::where('employee_id', $employeeId)

            ->whereBetween('work_date', [$period->start_date, $period->end_date])

            ->where(function($query) {

                $query->where('remarks', 'LIKE', '%Special Holiday%')

                      ->orWhere('remarks', 'LIKE', '%Special Non-Working%')

                      ->orWhere('status', 'LIKE', '%special_holiday%')

                      ->orWhere('status', 'LIKE', '%special_non_working%');

            })

            ->count();

    }

    $data = [

        'period' => $period->description ?? '',

        'employee_name' => $payroll->employee->full_name ?? '',

        'daily_rate' => (float) ($payroll->daily_rate ?? 0),

        'position' => $payroll->employee->position->position_name ?? '',

        'date_generated' => now()->format('F d, Y'),

        'days_worked' => (float) ($payroll->days_worked ?? 0),

        'legal_holidays' => $legalHolidaysCount,

        'special_holidays' => $specialHolidaysCount,

        'basic_salary' => (float) ($payroll->basic_salary ?? 0),

        'gross_pay' => (float) ($payroll->gross_pay ?? 0),

        'undertime_hours' => (float) ($payroll->undertime_hours ?? 0),

        'undertime_deduction' => (float) ($payroll->undertime_deduction ?? 0),

        'overtime_salary' => (float) ($payroll->overtime_salary ?? 0),

        'night_diff_salary' => (float) ($payroll->night_diff_salary ?? 0),

        'night_diff_ot_salary' => (float) ($payroll->night_diff_ot_salary ?? 0),

        'rest_day_ot_salary' => (float) ($payroll->rest_day_ot_salary ?? 0),

        'sunday_ot_salary' => (float) ($payroll->sunday_ot_salary ?? 0),

        'sss_ee' => (float) ($contribution->sss_ee ?? 0),

        'philhealth_ee' => (float) ($contribution->philhealth_ee ?? 0),

        'pagibig_ee' => (float) ($contribution->pagibig_ee ?? 0),

        'premium_voluntary_ss_contribution' => (float) ($contribution->premium_voluntary_ss_contribution ?? 0),

        'sss_salary_loan' => (float) ($contribution->sss_salary_loan ?? 0),

        'sss_calamity_loan' => (float) ($contribution->sss_calamity_loan ?? 0),

        'pagibig_salary_loan' => (float) ($contribution->pagibig_salary_loan ?? 0),

        'cash_advance' => (float) ($payroll->cash_advance ?? 0),

        'shortages' => (float) ($payroll->shortages ?? 0),

        'other_deduction' => (float) ($payroll->other_deduction ?? 0),

        'other_incentives' => (float) ($payroll->other_incentives ?? 0),

    ];

    $startDay = 16;

    if (isset($data['period'])) {

        preg_match('/(\d+)-(\d+)/', $data['period'], $matches);

        $startDay = (int) ($matches[1] ?? 16);

    }

    $isFirstCutoff = $startDay >= 1 && $startDay <= 15;

    $isSecondCutoff = $startDay >= 16;

    /*
     * The saved basic_salary includes both regular salary and holiday pay.
     * We only separate the presentation of both components.
     *
     * LEGAL HOLIDAY   : holiday salary = 1 daily rate per legal holiday day.
     * SPECIAL HOLIDAY : holiday salary = ONLY the premium (daily rate x 30%).
     *                   The normal daily rate for that day stays in Basic
     *                   Salary and the day counts as a worked day.
     *
     * Example: 700 daily rate, worked on a special holiday
     *          saved basic_salary = 910
     *          -> Basic Salary   = 700
     *          -> Holiday Salary = 210
     */
    $dailyRate = (float) ($data['daily_rate'] ?? 0);
    $combinedBasicSalary = (float) ($data['basic_salary'] ?? 0);
    $legalHolidaysCount = max(0, (int) ($data['legal_holidays'] ?? 0));
    $specialHolidaysCount = max(0, (int) ($data['special_holidays'] ?? 0));
    $undertime_deduction = (float) ($data['undertime_deduction'] ?? 0);

    // Special holiday premium only (e.g. 700 x 30% = 210).
    $specialHolidaySalary = ($dailyRate > 0 && $specialHolidaysCount > 0)
        ? min(
            $combinedBasicSalary,
            round($specialHolidaysCount * $dailyRate * $specialHolidayRate, 2)
        )
        : 0;

    // Remove the special premium first so it is not counted as an
    // extra paid day (910 - 210 = 700 -> 1 paid day, not 1.3).
    $salaryWithoutSpecial = $combinedBasicSalary - $specialHolidaySalary;

    $totalPaidDays = $dailyRate > 0
        ? max(0, (int) round($salaryWithoutSpecial / $dailyRate))
        : 0;

    // Legal holiday days are paid days that were not worked.
    $legalHolidayDays = ($legalHolidaysCount > 0 && $totalPaidDays > 0)
        ? min($legalHolidaysCount, $totalPaidDays)
        : 0;

    // Special holiday days are worked days, so they stay in worked days.
    $specialHolidayDays = ($specialHolidaysCount > 0 && $totalPaidDays > 0)
        ? min($specialHolidaysCount, max(0, $totalPaidDays - $legalHolidayDays))
        : 0;

    // Holidays column = legal + special.
    $holidayDays = $legalHolidayDays + $specialHolidayDays;

    // Regular working days (includes special holiday days worked).
    $calculatedDaysWorked = max(0, $totalPaidDays - $legalHolidayDays);

    // Legal holiday pay = full daily rate per holiday day.
    $legalHolidaySalary = round($legalHolidayDays * $dailyRate, 2);

    // Holiday Salary = legal holiday pay + special premium only.
    $holidaySalary = min(
        $combinedBasicSalary,
        round($legalHolidaySalary + $specialHolidaySalary, 2)
    );

    // Basic salary = saved salary minus the holiday component.
    $basicSalary = round($combinedBasicSalary - $holidaySalary, 2);

    // Label detail, e.g. "2: 1 legal, 1 special"
    $holidayLabelParts = [];

    if ($legalHolidayDays > 0) {
        $holidayLabelParts[] = $legalHolidayDays . ' legal';
    }

    if ($specialHolidayDays > 0) {
        $holidayLabelParts[] = $specialHolidayDays . ' special';
    }

    $holidayLabel = $holidayDays
        . (count($holidayLabelParts)
            ? ': ' . implode(', ', $holidayLabelParts)
            : '');

    $sss_ee = $isFirstCutoff ? ($data['sss_ee'] ?? 0) : 0;

    $philhealth_ee = $isFirstCutoff ? ($data['philhealth_ee'] ?? 0) : 0;

    $pagibig_ee = $isFirstCutoff ? ($data['pagibig_ee'] ?? 0) : 0;

    $premium_ss = $isFirstCutoff ? ($data['premium_voluntary_ss_contribution'] ?? 0) : 0;

    $sss_salary_loan = $isSecondCutoff ? ($data['sss_salary_loan'] ?? 0) : 0;

    $sss_calamity_loan = $isSecondCutoff ? ($data['sss_calamity_loan'] ?? 0) : 0;

    $pagibig_salary_loan = $isSecondCutoff ? ($data['pagibig_salary_loan'] ?? 0) : 0;

    $cash_advance = $data['cash_advance'] ?? 0;

    $shortages = $data['shortages'] ?? 0;

    $other_deduction = $data['other_deduction'] ?? 0;

    $other_incentives = $data['other_incentives'] ?? 0;

    $total_deductions = $sss_ee + $philhealth_ee + $pagibig_ee + $premium_ss +

                        $sss_salary_loan + $sss_calamity_loan + $pagibig_salary_loan +

                        $cash_advance + $shortages + $other_deduction + $undertime_deduction;

    $final_gross_pay = $data['gross_pay'] ?? 0;

    $final_net_pay = $final_gross_pay - $total_deductions;

@endphp

<div class="payslip-page">

    <div class="header">

        <h1>E.A OCAMPO ENTERPRISES</h1>

        <p>PAYROLL PERIOD: {{ $data['period'] ?? '' }}</p>

    </div>

    <table class="no-border">

        <tr>

            <td><strong>Name:</strong> {{ $data['employee_name'] ?? '' }}</td>

            <td><strong>Daily Rate:</strong> PHP {{ number_format($dailyRate, 2) }}</td>

        </tr>

        <tr>

            <td><strong>Position:</strong> {{ $data['position'] ?? '' }}</td>

            <td><strong>Date:</strong> {{ $data['date_generated'] ?? '' }}</td>

        </tr>

    </table>

    <table class="earnings-table">

        <tr class="section">

            <td>Description</td>

            <td class="right">Amount</td>

        </tr>

        <tr>
            <td>Basic Salary ({{ $calculatedDaysWorked }})</td>
            <td class="right">PHP {{ number_format($basicSalary, 2) }}</td>
        </tr>

        <tr>
            <td>Holidays ({{ $holidayLabel }})</td>
            <td class="right">PHP {{ number_format($holidaySalary, 2) }}</td>
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

            <td>

                Undertime Deduction

                (PHP {{ number_format(($dailyRate / 8), 2) }} × {{ $data['undertime_hours'] ?? 0 }} hrs)

            </td>

            <td class="right">PHP {{ number_format($undertime_deduction, 2) }}</td>

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

</div>

@endforeach

</body>

</html>