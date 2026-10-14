
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Payslip</title>

    <style>
        @page {
            size: A4;
            margin: 15px;
        }

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

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

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
    </style>
</head>

@php

    /*
    |--------------------------------------------------------------------------
    | PAYROLL PERIOD
    |--------------------------------------------------------------------------
    */

    $startDay = 16;

    if (isset($data['period'])) {
        preg_match(
            '/(\d+)-(\d+)/',
            $data['period'],
            $matches
        );

        $startDay = (int) ($matches[1] ?? 16);
    }

    $isFirstCutoff = $startDay >= 1 && $startDay <= 15;
    $isSecondCutoff = $startDay >= 16;


    /*
    |--------------------------------------------------------------------------
    | BASIC SALARY AND HOLIDAY SALARY
    |--------------------------------------------------------------------------
    |
    | The saved basic_salary contains regular salary + holiday salary.
    | We only separate the presentation of both components.
    |
    */

    $dailyRate = (float) ($data['daily_rate'] ?? 0);

    $combinedBasicSalary = (float) (
        $data['basic_salary'] ?? 0
    );

    $legalHolidaysCount = max(
        0,
        (int) ($data['legal_holidays'] ?? 0)
    );

    // Total paid units based on saved salary
    $totalPaidDays = $dailyRate > 0
        ? max(
            0,
            (int) round($combinedBasicSalary / $dailyRate)
        )
        : 0;

    /*
    | Preserve the existing holiday detection.
    |
    | Example:
    | 15 total paid days
    | 1 holiday
    | 14 regular working days
    */

    // Count holidays only if recorded in the DTR
if ($legalHolidaysCount > 0 && $totalPaidDays > 0) {

    $holidayDays = min(
        $legalHolidaysCount,
        $totalPaidDays
    );

    $calculatedDaysWorked = max(
        0,
        $totalPaidDays - $holidayDays
    );

} else {

    // No holiday recorded in DTR
    $holidayDays = 0;

    // All paid days are regular working days
    $calculatedDaysWorked = max(
        0,
        $totalPaidDays
    );

}

    /*
    |--------------------------------------------------------------------------
    | SEPARATE SALARY COMPUTATION
    |--------------------------------------------------------------------------
    */

    $holidaySalary = round(
        $holidayDays * $dailyRate,
        2
    );

    $basicSalary = round(
        $combinedBasicSalary - $holidaySalary,
        2
    );


    /*
    |--------------------------------------------------------------------------
    | UNDERTIME
    |--------------------------------------------------------------------------
    */

    $undertimeHours = (float) (
        $data['undertime_hours'] ?? 0
    );

    $undertime_deduction = (float) (
        $data['undertime_deduction'] ?? 0
    );


    /*
    |--------------------------------------------------------------------------
    | STATUTORY CONTRIBUTIONS - FIRST CUTOFF
    |--------------------------------------------------------------------------
    */

    $sss_ee = $isFirstCutoff
        ? (float) ($data['sss_ee'] ?? 0)
        : 0;

    $philhealth_ee = $isFirstCutoff
        ? (float) ($data['philhealth_ee'] ?? 0)
        : 0;

    $pagibig_ee = $isFirstCutoff
        ? (float) ($data['pagibig_ee'] ?? 0)
        : 0;

    $premium_ss = $isFirstCutoff
        ? (float) (
            $data['premium_voluntary_ss_contribution'] ?? 0
        )
        : 0;


    /*
    |--------------------------------------------------------------------------
    | LOANS - SECOND CUTOFF
    |--------------------------------------------------------------------------
    */

    $sss_salary_loan = $isSecondCutoff
        ? (float) ($data['sss_salary_loan'] ?? 0)
        : 0;

    $sss_calamity_loan = $isSecondCutoff
        ? (float) ($data['sss_calamity_loan'] ?? 0)
        : 0;

    $pagibig_salary_loan = $isSecondCutoff
        ? (float) ($data['pagibig_salary_loan'] ?? 0)
        : 0;


    /*
    |--------------------------------------------------------------------------
    | OTHER EARNINGS AND DEDUCTIONS
    |--------------------------------------------------------------------------
    */

    $cash_advance = (float) (
        $data['cash_advance'] ?? 0
    );

    $shortages = (float) (
        $data['shortages'] ?? 0
    );

    $other_deduction = (float) (
        $data['other_deduction'] ?? 0
    );

    $other_incentives = (float) (
        $data['other_incentives'] ?? 0
    );


    /*
    |--------------------------------------------------------------------------
    | TOTAL DEDUCTIONS
    |--------------------------------------------------------------------------
    */

    $total_deductions =
        $sss_ee
        + $philhealth_ee
        + $pagibig_ee
        + $premium_ss
        + $sss_salary_loan
        + $sss_calamity_loan
        + $pagibig_salary_loan
        + $cash_advance
        + $shortages
        + $other_deduction
        + $undertime_deduction;


    /*
    |--------------------------------------------------------------------------
    | FINAL GROSS AND NET PAY
    |--------------------------------------------------------------------------
    */

    $final_gross_pay = (float) (
        $data['gross_pay'] ?? 0
    );

    $final_net_pay =
        $final_gross_pay - $total_deductions;

@endphp

<body>

    <!-- HEADER -->

    <div class="header">

        <h1>E.A OCAMPO ENTERPRISES</h1>

        <p>
            PAYROLL PERIOD:
            {{ $data['period'] ?? '' }}
        </p>

    </div>


    <!-- EMPLOYEE INFORMATION -->

    <table class="no-border">

        <tr>
            <td>
                <strong>Name:</strong>
                {{ $data['employee_name'] ?? '' }}
            </td>

            <td>
                <strong>Daily Rate:</strong>
                PHP {{ number_format($dailyRate, 2) }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>Position:</strong>
                {{ $data['position'] ?? '' }}
            </td>

            <td>
                <strong>Date:</strong>
                {{ $data['date_generated'] ?? '' }}
            </td>
        </tr>

    </table>


    <!-- EARNINGS -->

    <table class="earnings-table">

        <tr class="section">
            <td>Description</td>
            <td class="right">Amount</td>
        </tr>


        <!-- BASIC SALARY -->

        <tr>
            <td>
                Basic Salary
                ({{ $calculatedDaysWorked }})
            </td>

            <td class="right">
                PHP {{ number_format($basicSalary, 2) }}
            </td>
        </tr>


        <!-- HOLIDAY SALARY -->

        <tr>
            <td>
                Holidays
                ({{ $holidayDays }})
            </td>

            <td class="right">
                PHP {{ number_format($holidaySalary, 2) }}
            </td>
        </tr>


        <!-- REGULAR OVERTIME -->

        <tr>
            <td>Regular Overtime Pay</td>

            <td class="right">
                PHP {{ number_format(
                    $data['overtime_salary'] ?? 0,
                    2
                ) }}
            </td>
        </tr>


        <!-- NIGHT DIFFERENTIAL -->

        <tr>
            <td>Night Differential Pay</td>

            <td class="right">
                PHP {{ number_format(
                    $data['night_diff_salary'] ?? 0,
                    2
                ) }}
            </td>
        </tr>


        <!-- NIGHT DIFFERENTIAL OT -->

        <tr>
            <td>Night Differential OT Pay</td>

            <td class="right">
                PHP {{ number_format(
                    $data['night_diff_ot_salary'] ?? 0,
                    2
                ) }}
            </td>
        </tr>


        <!-- REST DAY OT -->

        <tr>
            <td>Rest Day OT Pay</td>

            <td class="right">
                PHP {{ number_format(
                    $data['rest_day_ot_salary'] ?? 0,
                    2
                ) }}
            </td>
        </tr>


        <!-- SUNDAY OT -->

        <tr>
            <td>Sunday OT Pay</td>

            <td class="right">
                PHP {{ number_format(
                    $data['sunday_ot_salary'] ?? 0,
                    2
                ) }}
            </td>
        </tr>


        <!-- OTHER INCENTIVES -->

        <tr>
            <td>Other Incentives</td>

            <td class="right">
                PHP {{ number_format(
                    $other_incentives,
                    2
                ) }}
            </td>
        </tr>


        <!-- GROSS PAY -->

        <tr class="bold">
            <td>GROSS PAY</td>

            <td class="right">
                PHP {{ number_format(
                    $final_gross_pay,
                    2
                ) }}
            </td>
        </tr>

    </table>


    <!-- DEDUCTIONS -->

    <table class="deductions-table">

        <tr class="section">
            <td colspan="2">
                DEDUCTIONS
            </td>
        </tr>


        <!-- UNDERTIME -->

        <tr>
            <td>
                Undertime Deduction
                (PHP {{ number_format(
                    $dailyRate / 8,
                    2
                ) }}
                × {{ $undertimeHours }} hrs)
            </td>

            <td class="right">
                PHP {{ number_format(
                    $undertime_deduction,
                    2
                ) }}
            </td>
        </tr>


        <!-- SSS EE -->

        <tr>
            <td>SSS (EE Share)</td>

            <td class="right">
                PHP {{ number_format($sss_ee, 2) }}
            </td>
        </tr>


        <!-- PHILHEALTH EE -->

        <tr>
            <td>PhilHealth (EE Share)</td>

            <td class="right">
                PHP {{ number_format($philhealth_ee, 2) }}
            </td>
        </tr>


        <!-- PAGIBIG EE -->

        <tr>
            <td>Pag-IBIG (EE Share)</td>

            <td class="right">
                PHP {{ number_format($pagibig_ee, 2) }}
            </td>
        </tr>


        <!-- PREMIUM SS -->

        <tr>
            <td>SSS Premium Contribution</td>

            <td class="right">
                PHP {{ number_format($premium_ss, 2) }}
            </td>
        </tr>


        <!-- SSS SALARY LOAN -->

        <tr>
            <td>SSS Salary Loan</td>

            <td class="right">
                PHP {{ number_format(
                    $sss_salary_loan,
                    2
                ) }}
            </td>
        </tr>


        <!-- SSS CALAMITY LOAN -->

        <tr>
            <td>SSS Calamity Loan</td>

            <td class="right">
                PHP {{ number_format(
                    $sss_calamity_loan,
                    2
                ) }}
            </td>
        </tr>


        <!-- PAGIBIG LOAN -->

        <tr>
            <td>Pag-IBIG Loan</td>

            <td class="right">
                PHP {{ number_format(
                    $pagibig_salary_loan,
                    2
                ) }}
            </td>
        </tr>


        <!-- CASH ADVANCE -->

        <tr>
            <td>Cash Advance</td>

            <td class="right">
                PHP {{ number_format(
                    $cash_advance,
                    2
                ) }}
            </td>
        </tr>


        <!-- SHORTAGES -->

        <tr>
            <td>Shortages</td>

            <td class="right">
                PHP {{ number_format(
                    $shortages,
                    2
                ) }}
            </td>
        </tr>


        <!-- OTHER DEDUCTION -->

        <tr>
            <td>Other Deduction</td>

            <td class="right">
                PHP {{ number_format(
                    $other_deduction,
                    2
                ) }}
            </td>
        </tr>


        <!-- TOTAL DEDUCTIONS -->

        <tr class="bold">
            <td>TOTAL DEDUCTIONS</td>

            <td class="right">
                PHP {{ number_format(
                    $total_deductions,
                    2
                ) }}
            </td>
        </tr>

    </table>


    <!-- NET PAY -->

    <table>

        <tr class="net">

            <td>NET PAY</td>

            <td class="right">
                PHP {{ number_format(
                    $final_net_pay,
                    2
                ) }}
            </td>

        </tr>

    </table>


    <!-- SIGNATURES -->

    <table class="no-border signature">

        <tr>

            <td class="center">

                _________________________
                <br>

                Authorized Signature

            </td>

            <td class="center">

                _________________________
                <br>

                Employee Signature

            </td>

        </tr>

    </table>


    <!-- FOOTER -->

    <div class="footer">
        This payslip is system generated.
    </div>

</body>
</html>
