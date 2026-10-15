<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payroll Summary</title>
    <style>
        /* Dompdf / A3 landscape: 28 report columns */
        @page { size: A4 landscape; margin: 6mm 6mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; margin: 0; }
        h3 { font-size: 12px; }
        h4 { font-size: 9px; }
        table { border-collapse: collapse; width: 100%; margin-top: 9px; }
        th, td { border: 0.5px solid #444; padding: 2px 2px; vertical-align: middle; }
        th { background: #f0f0f0; font-weight: bold; text-align: center; }
        td { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .page-break { page-break-after: always; }
        .grand-summary { table-layout: fixed; font-size: 6.7px; }
        .grand-summary th, .grand-summary td { overflow-wrap: break-word; word-wrap: break-word; }
        .detail-report { table-layout: fixed; font-size: 8.2px; margin-top: 3px; }
        .detail-report th { font-size: 8px; line-height: 1.18; }
        .detail-report td { font-size: 8.4px; line-height: 1.18; white-space: nowrap; }
        .detail-report th, .detail-report td { padding: 3px 2px; word-wrap: break-word; overflow-wrap: break-word; }
        .detail-report th:first-child, .detail-report td:first-child { width: 16%; text-align: left; white-space: normal; }
        .detail-report thead { display: table-header-group; }
        .detail-report tr { page-break-inside: avoid; }
        .branch-page { page-break-before: always; }
        .branch-caption { margin: 3px 0 6px; }
        .brand { margin: 0; text-align: center; }
        .section-title { text-align: left; font-size: 10px; margin: 9px 0 2px; }
        .signature-table { margin-top: 12px; }
        .total-row { background: #f4f4f4; }
        .signature-line { border-top: 1px solid #000; width: 200px; margin-top: 35px; }
        .no-border td { border: none; }
    </style>
</head>
<body>
{{-- GRAND SUMMARY PAGE --}}
<div class="page-break">
    <h3 style="text-align: center; margin-bottom: 0;">
        E.A OCAMPO ENTERPRISES
    </h3>
    <h4 style="text-align: center; margin-top: 5px;">
        GRAND PAYROLL SUMMARY - {{ $period->description }}
    </h4>
    @php
        // Reporting split only: the saved basic_salary includes holiday pay.
        // This assumes one daily rate per holiday day. Confirm the holiday
        // rate rule in the payroll generation logic before using for accounting.
        $splitSalary = function ($payroll) use ($period) {
            $dailyRate = (float) ($payroll->daily_rate ?? 0);
            $combinedSalary = (float) ($payroll->basic_salary ?? 0);
            $employeeId = data_get($payroll, 'employee.id');

            $legalHolidaysCount = $employeeId
                ? \App\Models\DailyTimeRecord::query()
                    ->where('employee_id', $employeeId)
                    ->whereBetween('work_date', [$period->start_date, $period->end_date])
                    ->where(function ($query) {
                        $query->where('remarks', 'LIKE', '%Legal Holiday%')
                            ->orWhere('status', 'LIKE', '%legal_holiday%');
                    })
                    ->count()
                : 0;

            $totalPaidUnits = $dailyRate > 0
                ? max(0, (int) round($combinedSalary / $dailyRate))
                : 0;

            // No automatic holiday allocation for a full 15-day period.
            // Holiday markers are taken from the DTR query above.
            if ($legalHolidaysCount > 0 && $totalPaidUnits > 0) {
                $holidayDays = min(
                    max(0, (int) $legalHolidaysCount),
                    $totalPaidUnits
                );

                $daysWorked = max(0, $totalPaidUnits - $holidayDays);
            } else {
                $holidayDays = 0;
                $daysWorked = $totalPaidUnits;
            }

            // Reporting assumes a standard 15-day payroll period.
            $daysAbsent = max(0, 15 - $totalPaidUnits);

            // Holiday component is split from, not added to, saved salary.
            $holidaySalary = min($combinedSalary, round($holidayDays * $dailyRate, 2));
            $basicSalaryOnly = round($combinedSalary - $holidaySalary, 2);

            return [
                'basic' => $basicSalaryOnly,
                'holiday' => $holidaySalary,
                'holiday_days' => $holidayDays,
                'days_worked' => $daysWorked,
                'days_absent' => max(0, $daysAbsent),
            ];
        };

        $overallTotal = [
            'basic' => 0,
            'holiday' => 0,
            'gross' => 0,
            'sss_loan' => 0,
            'sss_cal' => 0,
            'pi_loan' => 0,
            'ca' => 0,
            'short' => 0,
            'undertime' => 0,
            'other' => 0,
            'sss' => 0,
            'prem' => 0,
            'ph' => 0,
            'pi' => 0,
            'deductions' => 0,
            'net' => 0,
        ];
        $startDay = \Carbon\Carbon::parse($period->start_date)->day;
        $isFirst = $startDay >= 1 && $startDay <= 15;
        $isSecond = $startDay >= 16;
    @endphp
    <table class="grand-summary">
        <thead>
            <tr>
                <th class="text-left">Branch</th>
                <th>Total Basic Salary</th>
                <th>Total Holiday Salary</th>
                <th>Total Gross Pay</th>
                <th>SSS Salary Loan</th>
                <th>SSS Calamity Loan</th>
                <th>PagIBIG Salary Loan</th>
                <th>Total Cash Advance</th>
                <th>Total Shortages</th>
                <th>Undertime Deduction</th>
                <th>Other Deductions</th>
                <th>Total SSS (ER+EE)</th>
                <th>Total Premium SS</th>
                <th>Total PhilHealth (ER+EE)</th>
                <th>Total PagIBIG (ER+EE)</th>
                <th>Total Deductions</th>
                <th>Total Net Pay</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($groupedPayrolls as $branchName => $payrolls)
                @php
                    $bBasic = 0;
                    $bHoliday = 0;
                    $bGross = 0;
                    $bSssL = 0;
                    $bSssC = 0;
                    $bPiL = 0;
                    $bCa = 0;
                    $bShort = 0;
                    $bUndertime = 0;
                    $bOther = 0;
                    $bSss = 0;
                    $bPrem = 0;
                    $bPh = 0;
                    $bPi = 0;
                    $bDeductions = 0;
                    $bNet = 0;
                    foreach ($payrolls as $payroll) {
                        /*
                         * Use the finalized gross pay, total deductions,
                         * and net pay saved in the payroll record.
                         */
                        $storedGrossPay = (float) ($payroll->gross_pay ?? 0);
                        $storedNetPay = (float) ($payroll->net_pay ?? 0);
                        $storedTotalDeductions =
                            $payroll->total_deductions !== null
                                ? (float) $payroll->total_deductions
                                : max(
                                    0,
                                    $storedGrossPay - $storedNetPay
                                );
                        /*
                         * Calculate undertime deduction.
                         *
                         * Example:
                         * ₱600 daily rate / 8 hours × 1.5 UT hours
                         * = ₱112.50 undertime deduction.
                         */
                        $undertimeHours =
                            (float) ($payroll->undertime_hours ?? 0);
                        $dailyRate =
                            (float) ($payroll->daily_rate ?? 0);
                        $undertimeDeduction =
                            $payroll->undertime_deduction !== null
                                ? (float) $payroll->undertime_deduction
                                : (
                                    $dailyRate > 0
                                        ? round(
                                            ($dailyRate / 8)
                                            * $undertimeHours,
                                            2
                                        )
                                        : 0
                                );
                        $salaryParts = $splitSalary($payroll);
                        $bBasic += $salaryParts['basic'];
                        $bHoliday += $salaryParts['holiday'];
                        $bGross += $storedGrossPay;
                        $bCa +=
                            (float) ($payroll->cash_advance ?? 0);
                        $bShort +=
                            (float) ($payroll->shortages ?? 0);
                        $bUndertime += $undertimeDeduction;
                        $bOther +=
                            (float) ($payroll->other_deduction ?? 0);
                        $bDeductions += $storedTotalDeductions;
                        $bNet += $storedNetPay;
                        /*
                         * Second-half deductions.
                         */
                        $sssSalaryLoan = $isSecond
                            ? (float) (
                                $payroll->contribution
                                    ?->sss_salary_loan ?? 0
                            )
                            : 0;
                        $sssCalamityLoan = $isSecond
                            ? (float) (
                                $payroll->contribution
                                    ?->sss_calamity_loan ?? 0
                            )
                            : 0;
                        $pagibigSalaryLoan = $isSecond
                            ? (float) (
                                $payroll->contribution
                                    ?->pagibig_salary_loan ?? 0
                            )
                            : 0;
                        $bSssL += $sssSalaryLoan;
                        $bSssC += $sssCalamityLoan;
                        $bPiL += $pagibigSalaryLoan;
                        /*
                         * First-half contributions.
                         */
                        $sssEe = $isFirst
                            ? (float) (
                                $payroll->contribution?->sss_ee ?? 0
                            )
                            : 0;
                        $sssEr = $isFirst
                            ? (float) (
                                $payroll->contribution?->sss_er ?? 0
                            )
                            : 0;
                        $bSss += $sssEe + $sssEr;
                        $premium = $isFirst
                            ? (float) (
                                $payroll->contribution
                                    ?->premium_voluntary_ss_contribution
                                    ?? 0
                            )
                            : 0;
                        $bPrem += $premium;
                        $philhealthEe = $isFirst
                            ? (float) (
                                $payroll->contribution
                                    ?->philhealth_ee ?? 0
                            )
                            : 0;
                        $philhealthEr = $isFirst
                            ? (float) (
                                $payroll->contribution
                                    ?->philhealth_er ?? 0
                            )
                            : 0;
                        $bPh += $philhealthEe + $philhealthEr;
                        $pagibigEe = $isFirst
                            ? (float) (
                                $payroll->contribution
                                    ?->pagibig_ee ?? 0
                            )
                            : 0;
                        $pagibigEr = $isFirst
                            ? (float) (
                                $payroll->contribution
                                    ?->pagibig_er ?? 0
                            )
                            : 0;
                        $bPi += $pagibigEe + $pagibigEr;
                    }
                    $overallTotal['basic'] += $bBasic;
                    $overallTotal['holiday'] += $bHoliday;
                    $overallTotal['gross'] += $bGross;
                    $overallTotal['sss_loan'] += $bSssL;
                    $overallTotal['sss_cal'] += $bSssC;
                    $overallTotal['pi_loan'] += $bPiL;
                    $overallTotal['ca'] += $bCa;
                    $overallTotal['short'] += $bShort;
                    $overallTotal['undertime'] += $bUndertime;
                    $overallTotal['other'] += $bOther;
                    $overallTotal['sss'] += $bSss;
                    $overallTotal['prem'] += $bPrem;
                    $overallTotal['ph'] += $bPh;
                    $overallTotal['pi'] += $bPi;
                    $overallTotal['deductions'] += $bDeductions;
                    $overallTotal['net'] += $bNet;
                @endphp
                <tr>
                    <td class="text-left">
                        {{ $branchName ?? 'No Branch' }}
                    </td>
                    <td>{{ number_format($bBasic, 2) }}</td>
                    <td>{{ number_format($bHoliday, 2) }}</td>
                    <td>{{ number_format($bGross, 2) }}</td>
                    <td>{{ number_format($bSssL, 2) }}</td>
                    <td>{{ number_format($bSssC, 2) }}</td>
                    <td>{{ number_format($bPiL, 2) }}</td>
                    <td>{{ number_format($bCa, 2) }}</td>
                    <td>{{ number_format($bShort, 2) }}</td>
                    <td>{{ number_format($bUndertime, 2) }}</td>
                    <td>{{ number_format($bOther, 2) }}</td>
                    <td>{{ number_format($bSss, 2) }}</td>
                    <td>{{ number_format($bPrem, 2) }}</td>
                    <td>{{ number_format($bPh, 2) }}</td>
                    <td>{{ number_format($bPi, 2) }}</td>
                    <td>{{ number_format($bDeductions, 2) }}</td>
                    <td class="bold">
                        {{ number_format($bNet, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bold">
                <td class="text-left">
                    TOTAL (ALL BRANCHES)
                </td>
                <td>
                    {{ number_format($overallTotal['basic'], 2) }}
                </td>

                <td>
                    {{ number_format($overallTotal['holiday'], 2) }}
                </td>
                <td>
                    {{ number_format($overallTotal['gross'], 2) }}
                </td>
                <td>
                    {{ number_format($overallTotal['sss_loan'], 2) }}
                </td>
                <td>
                    {{ number_format($overallTotal['sss_cal'], 2) }}
                </td>
                <td>
                    {{ number_format($overallTotal['pi_loan'], 2) }}
                </td>
                <td>
                    {{ number_format($overallTotal['ca'], 2) }}
                </td>
                <td>
                    {{ number_format($overallTotal['short'], 2) }}
                </td>
                <td>
                    {{ number_format($overallTotal['undertime'], 2) }}
                </td>
                <td>
                    {{ number_format($overallTotal['other'], 2) }}
                </td>
                <td>
                    {{ number_format($overallTotal['sss'], 2) }}
                </td>
                <td>
                    {{ number_format($overallTotal['prem'], 2) }}
                </td>
                <td>
                    {{ number_format($overallTotal['ph'], 2) }}
                </td>
                <td>
                    {{ number_format($overallTotal['pi'], 2) }}
                </td>
                <td>
                    {{ number_format(
                        $overallTotal['deductions'],
                        2
                    ) }}
                </td>
                <td style="background-color: #eee;">
                    {{ number_format($overallTotal['net'], 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
{{-- INDIVIDUAL BRANCH DETAILS: Two readable tables per branch --}}
@foreach ($groupedPayrolls as $branchName => $payrolls)
    <div class="branch-page">
        <h3 class="center brand">E.A OCAMPO ENTERPRISES</h3>
        <div class="branch-caption">Payroll Summary - {{ $period->description }}<br>Branch: {{ $branchName ?? 'No Branch' }}</div>
        @php
            $columns = [
                'days_worked',
                'holiday_days',
                'days_absent',
                'undertime_hours',
                'undertime_deduction',
                'daily_rate',
                'basic_salary',
                'holiday_salary',
                'overtime_salary',
                'gross_pay',
                'other_incentives',
                'cash_advance',
                'shortages',
                'other_deduction',
                'sss_er',
                'sss_ee',
                'premium_voluntary_ss_contribution',
                'sss_salary_loan',
                'sss_calamity_loan',
                'philhealth_er',
                'philhealth_ee',
                'pagibig_er',
                'pagibig_ee',
                'pagibig_salary_loan',
                'total_deductions',
                'net_pay',
            ];
            $admin = array_fill_keys($columns, 0);
            $field = array_fill_keys($columns, 0);
        @endphp
        @php
            $reportRows = [];
        @endphp
        @foreach ($payrolls as $payroll)
            @php
                $rowValues = [];
                        $type = data_get(
                            $payroll,
                            'employee.employmentTypes.name'
                        )
                            ?? data_get(
                                $payroll,
                                'employee.employmentType.name'
                            )
                            ?? data_get(
                                $payroll,
                                'employee.employee_type'
                            )
                            ?? data_get(
                                $payroll,
                                'employee.employment_type'
                            )
                            ?? 'Field';
                        $category =
                            strtolower(trim($type)) === 'admin'
                                ? 'admin'
                                : 'field';
                        /*
                         * First-half contributions.
                         */
                        $sssEe = $isFirst
                            ? (float) (
                                $payroll->contribution?->sss_ee ?? 0
                            )
                            : 0;
                        $sssEr = $isFirst
                            ? (float) (
                                $payroll->contribution?->sss_er ?? 0
                            )
                            : 0;
                        $philhealthEe = $isFirst
                            ? (float) (
                                $payroll->contribution
                                    ?->philhealth_ee ?? 0
                            )
                            : 0;
                        $philhealthEr = $isFirst
                            ? (float) (
                                $payroll->contribution
                                    ?->philhealth_er ?? 0
                            )
                            : 0;
                        $pagibigEe = $isFirst
                            ? (float) (
                                $payroll->contribution
                                    ?->pagibig_ee ?? 0
                            )
                            : 0;
                        $pagibigEr = $isFirst
                            ? (float) (
                                $payroll->contribution
                                    ?->pagibig_er ?? 0
                            )
                            : 0;
                        $premium = $isFirst
                            ? (float) (
                                $payroll->contribution
                                    ?->premium_voluntary_ss_contribution
                                    ?? 0
                            )
                            : 0;
                        /*
                         * Second-half loans.
                         */
                        $sssLoan = $isSecond
                            ? (float) (
                                $payroll->contribution
                                    ?->sss_salary_loan ?? 0
                            )
                            : 0;
                        $sssCalamityLoan = $isSecond
                            ? (float) (
                                $payroll->contribution
                                    ?->sss_calamity_loan ?? 0
                            )
                            : 0;
                        $pagibigLoan = $isSecond
                            ? (float) (
                                $payroll->contribution
                                    ?->pagibig_salary_loan ?? 0
                            )
                            : 0;
                        $cashAdvance =
                            (float) ($payroll->cash_advance ?? 0);
                        $shortages =
                            (float) ($payroll->shortages ?? 0);
                        $otherDeduction =
                            (float) ($payroll->other_deduction ?? 0);
                        $otherIncentives =
                            (float) ($payroll->other_incentives ?? 0);
                        /*
                         * Calculate undertime deduction.
                         */
                        $undertimeHours =
                            (float) ($payroll->undertime_hours ?? 0);
                        $dailyRate =
                            (float) ($payroll->daily_rate ?? 0);
                        $undertimeDeduction =
                            $payroll->undertime_deduction !== null
                                ? (float) $payroll->undertime_deduction
                                : (
                                    $dailyRate > 0
                                        ? round(
                                            ($dailyRate / 8)
                                            * $undertimeHours,
                                            2
                                        )
                                        : 0
                                );
                        $totalOvertime =
                            (float) (
                                $payroll->overtime_salary ?? 0
                            )
                            + (float) (
                                $payroll->sunday_ot_salary ?? 0
                            )
                            + (float) (
                                $payroll->rest_day_ot_salary ?? 0
                            )
                            + (float) (
                                $payroll->night_diff_salary ?? 0
                            )
                            + (float) (
                                $payroll->night_diff_ot_salary ?? 0
                            );
                        /*
                         * Use stored finalized values.
                         */
                        $storedGrossPay =
                            (float) ($payroll->gross_pay ?? 0);
                        $storedNetPay =
                            (float) ($payroll->net_pay ?? 0);
                        $storedTotalDeductions =
                            $payroll->total_deductions !== null
                                ? (float) $payroll->total_deductions
                                : max(
                                    0,
                                    $storedGrossPay - $storedNetPay
                                );
                        // Use the same salary breakdown as the grand summary.
                        $salaryParts = $splitSalary($payroll);
                        $basicSalary = $salaryParts['basic'];
                        $holidaySalary = $salaryParts['holiday'];
                        $holidayDays = $salaryParts['holiday_days'];
                        $calculatedDaysWorked = $salaryParts['days_worked'];
                        $calculatedDaysAbsent = $salaryParts['days_absent'];

                        /*
                         * Accumulate Admin and Field totals.
                         */
                        foreach ($columns as $column) {
                            $value = match ($column) {
                                'days_worked'
                                    => $calculatedDaysWorked,
                                'holiday_days'
                                    => $holidayDays,
                                'days_absent'
                                    => $calculatedDaysAbsent,
                                'undertime_hours'
                                    => $undertimeHours,
                                'undertime_deduction'
                                    => $undertimeDeduction,
                                'holiday_salary'
                                    => $holidaySalary,

                                'basic_salary'
                                    => $basicSalary,

                                'overtime_salary'
                                    => $totalOvertime,
                                'gross_pay'
                                    => $storedGrossPay,
                                'other_incentives'
                                    => $otherIncentives,
                                'sss_er'
                                    => $sssEr,
                                'sss_ee'
                                    => $sssEe,
                                'premium_voluntary_ss_contribution'
                                    => $premium,
                                'sss_salary_loan'
                                    => $sssLoan,
                                'sss_calamity_loan'
                                    => $sssCalamityLoan,
                                'philhealth_er'
                                    => $philhealthEr,
                                'philhealth_ee'
                                    => $philhealthEe,
                                'pagibig_er'
                                    => $pagibigEr,
                                'pagibig_ee'
                                    => $pagibigEe,
                                'pagibig_salary_loan'
                                    => $pagibigLoan,
                                'total_deductions'
                                    => $storedTotalDeductions,
                                'net_pay'
                                    => $storedNetPay,
                                default
                                    => (float) (
                                        $payroll->{$column} ?? 0
                                    ),
                            };
                            ${$category}[$column] += $value;
                            $rowValues[$column] = $value;
                        }
                $reportRows[] = [
                    'name' => data_get($payroll, 'employee.full_name', 'Unknown Employee'),
                    'values' => $rowValues,
                ];
            @endphp
        @endforeach
        @php
            $sections = [
                'ATTENDANCE & EARNINGS' => ['days_worked' => 'Days Worked', 'holiday_days' => 'Holiday', 'days_absent' => 'Days Absent', 'undertime_hours' => 'UT Hours', 'undertime_deduction' => 'UT Deduction', 'daily_rate' => 'Daily Rate', 'basic_salary' => 'Basic Salary', 'holiday_salary' => 'Holiday Pay', 'overtime_salary' => 'Overtime Pay', 'gross_pay' => 'Gross Pay', 'other_incentives' => 'Other Incentives', 'cash_advance' => 'Cash Advance', 'shortages' => 'Shortages', 'other_deduction' => 'Other Ded.'],
                'DEDUCTIONS & CONTRIBUTIONS' => ['sss_er' => 'SSS ER', 'sss_ee' => 'SSS EE', 'premium_voluntary_ss_contribution' => 'Prem SS', 'sss_salary_loan' => 'SSS Loan', 'sss_calamity_loan' => 'SSS Cal', 'philhealth_er' => 'PH ER', 'philhealth_ee' => 'PH EE', 'pagibig_er' => 'PAG ER', 'pagibig_ee' => 'PAG EE', 'pagibig_salary_loan' => 'PAG Loan', 'total_deductions' => 'Total Deduction', 'net_pay' => 'Net Pay'],
            ];
        @endphp
        @foreach ($sections as $sectionName => $sectionColumns)
            <h4 class="section-title">{{ $sectionName }}</h4>
            <table class="detail-report">
                <thead>
                    <tr>
                        <th class="text-left name-cell">Employee Name</th>
                        @foreach ($sectionColumns as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                        @if ($sectionName === 'DEDUCTIONS & CONTRIBUTIONS')
                            <th class="sign-cell">Signature</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportRows as $reportRow)
                        <tr>
                            <td class="text-left name-cell">{{ $reportRow['name'] }}</td>
                            @foreach ($sectionColumns as $column => $label)
                                <td>{{ number_format($reportRow['values'][$column] ?? 0, in_array($column, ['days_worked', 'holiday_days', 'days_absent'], true) ? 0 : 2) }}</td>
                            @endforeach
                            @if ($sectionName === 'DEDUCTIONS & CONTRIBUTIONS')
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                    @foreach (['TOTAL ADMIN' => $admin, 'TOTAL FIELD' => $field, 'TOTAL BRANCH' => null] as $totalLabel => $totals)
                        <tr class="bold total-row">
                            <td class="text-left">{{ $totalLabel }}</td>
                            @foreach ($sectionColumns as $column => $label)
                                <td>{{ number_format($totals === null ? ($admin[$column] + $field[$column]) : $totals[$column], in_array($column, ['days_worked', 'holiday_days', 'days_absent'], true) ? 0 : 2) }}</td>
                            @endforeach
                            @if ($sectionName === 'DEDUCTIONS & CONTRIBUTIONS')
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
        <table class="no-border signature-table">
            <tr>
                <td class="text-left"><div class="signature-line"></div>Prepared by:<br>Name and Signature</td>
                <td></td>
                <td class="text-left"><div class="signature-line"></div>Approved by:<br><strong>EDUARDO A. OCAMPO</strong><br>Authorized Signatory</td>
            </tr>
        </table>
    </div>
@endforeach
</body>
</html>
