<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>13th Month Payroll Summary - {{ $year }}</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #000;
            margin: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 7px;
        }

        th {
            text-align: center;
            font-weight: bold;
            background-color: #f2f2f2;
            text-transform: uppercase;
        }

        td {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .money-cell {
            white-space: nowrap;
        }

        .summary-cell {
            background-color: #fcfcfc;
            font-weight: bold;
            white-space: nowrap;
        }

        .mid-pay {
            background-color: #fff7ed;
        }

        .year-end-pay {
            background-color: #fefce8;
        }

        .whole-pay {
            background-color: #ecfdf5;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 180px;
            margin-top: 35px;
        }

        .no-border td {
            border: none;
        }

        /*
         * By default, browsers/print engines treat <tfoot> as a repeating
         * footer and print it at the bottom of EVERY page the table spans
         * across, not just the true end of the table. Forcing it back to
         * a normal row group keeps "TOTAL MASTER SUMMARY" appearing only
         * once, on the last page where the table actually ends.
         */
        tfoot {
            display: table-row-group;
        }

        .header-title {
            text-align: center;
            margin: 0 0 15px 0;
        }

        .small-text {
            font-size: 7px;
            color: #555;
        }
    </style>
</head>

<body>

@php
    $safeDividend = (float) ($dividend ?: 12);

    $formatMoney = function ($amount, $decimals = 2) {
        return '₱' . number_format((float) $amount, $decimals);
    };

    $formatMonth = function ($amount) {
        $amount = (float) $amount;

        return $amount > 0 ? number_format($amount, 0) : '-';
    };

    $getMonthTotal = function ($employees, $month) {
        return array_sum(array_map(
            fn ($employee) => (float) ($employee['months'][$month] ?? 0),
            $employees
        ));
    };

    $grandMidYearPay = (float) (
        $grand_totals['mid_year_pay']
        ?? (
            array_sum(array_map(function ($employee) {
                $total = 0;

                for ($month = 1; $month <= 6; $month++) {
                    $total += (float) ($employee['months'][$month] ?? 0);
                }

                return $total;
            }, $employees)) / $safeDividend
        )
    );

    $grandYearEndPay = (float) (
        $grand_totals['year_end_pay']
        ?? (
            array_sum(array_map(function ($employee) {
                $total = 0;

                for ($month = 7; $month <= 12; $month++) {
                    $total += (float) ($employee['months'][$month] ?? 0);
                }

                return $total;
            }, $employees)) / $safeDividend
        )
    );

    $grandWholeYearPay = (float) (
        $grand_totals['whole_year_pay']
        ?? $grand_totals['thirteenth']
        ?? 0
    );
@endphp

<div class="header-title">
    <h3 style="margin:0; font-size:14px;">E.A OCAMPO ENTERPRISES</h3>
    <h4 style="margin:4px 0 0 0; font-size:11px;">
        13TH MONTH PAYROLL SUMMARY — CALENDAR YEAR {{ $year }}
    </h4>
    <small class="small-text">
        Calculation Metric Basis: Gross Pay Earned Divided By {{ $dividend }}
    </small>
</div>

<table>
    <thead>
        <tr>
            <th class="text-left" style="width:150px;">Employee Name</th>

            <th>Jan</th>
            <th>Feb</th>
            <th>Mar</th>
            <th>Apr</th>
            <th>May</th>
            <th>Jun</th>

            <th>Jul</th>
            <th>Aug</th>
            <th>Sep</th>
            <th>Oct</th>
            <th>Nov</th>
            <th>Dec</th>

            <th>Total Gross Pay</th>
            <th>Mid-Year Pay<br><span class="small-text">Jan-Jun ÷ {{ $dividend }}</span></th>
            <th>Mid-Year Remarks</th>
            <th>Year-End Pay<br><span class="small-text">Jul-Dec ÷ {{ $dividend }}</span></th>
            <th>Year-End Remarks</th>
            <th>Whole Year Pay<br><span class="small-text">Jan-Dec ÷ {{ $dividend }}</span></th>
            <th style="width:110px;">Signature Receipt</th>
        </tr>
    </thead>

    <tbody>
        @foreach($employees as $emp)
            @php
                $midYearGross = 0;
                $yearEndGross = 0;

                for ($month = 1; $month <= 6; $month++) {
                    $midYearGross += (float) ($emp['months'][$month] ?? 0);
                }

                for ($month = 7; $month <= 12; $month++) {
                    $yearEndGross += (float) ($emp['months'][$month] ?? 0);
                }

                $totalGross = (float) ($emp['total_gross'] ?? ($midYearGross + $yearEndGross));

                $midYearPay = (float) ($emp['mid_year_pay'] ?? ($midYearGross / $safeDividend));
                $yearEndPay = (float) ($emp['year_end_pay'] ?? ($yearEndGross / $safeDividend));
                $wholeYearPay = (float) ($emp['whole_year_pay'] ?? $emp['thirteenth_pay'] ?? ($totalGross / $safeDividend));
            @endphp

            <tr>
                <td class="text-left bold">{{ $emp['name'] }}</td>

                @for($m = 1; $m <= 12; $m++)
                    <td>
                        {{ $formatMonth($emp['months'][$m] ?? 0) }}
                    </td>
                @endfor

                <td class="bold money-cell">
                    {{ $formatMoney($totalGross) }}
                </td>

                <td class="summary-cell mid-pay">
                    {{ $formatMoney($midYearPay) }}
                </td>

                <td>
                    {{ $emp['mid_year_remarks'] ?? '-' }}
                </td>

                <td class="summary-cell year-end-pay">
                    {{ $formatMoney($yearEndPay) }}
                </td>

                <td>
                    {{ $emp['year_end_remarks'] ?? '-' }}
                </td>

                <td class="summary-cell whole-pay">
                    {{ $formatMoney($wholeYearPay) }}
                </td>

                <td></td>
            </tr>
        @endforeach
    </tbody>

    <tfoot>
        <tr class="bold" style="background-color: #eaeded;">
            <td class="text-left">TOTAL MASTER SUMMARY</td>

            @for($m = 1; $m <= 12; $m++)
                @php
                    $monthlySum = $getMonthTotal($employees, $m);
                @endphp

                <td>
                    {{ $formatMonth($monthlySum) }}
                </td>
            @endfor

            <td class="money-cell">
                {{ $formatMoney($grand_totals['gross'] ?? 0) }}
            </td>

            <td class="money-cell mid-pay">
                {{ $formatMoney($grandMidYearPay) }}
            </td>

            <td></td>

            <td class="money-cell year-end-pay">
                {{ $formatMoney($grandYearEndPay) }}
            </td>

            <td></td>

            <td class="money-cell whole-pay">
                {{ $formatMoney($grandWholeYearPay) }}
            </td>

            <td></td>
        </tr>
    </tfoot>
</table>

<br><br>

<table class="no-border">
    <tr>
        <td class="text-left" style="width: 50%;">
            <div class="signature-line"></div>
            Prepared by:<br>
            Name and Signature
        </td>

        <td class="text-left" style="width: 50%;">
            <div class="signature-line"></div>
            Approved by:<br>
            <strong>EDUARDO A. OCAMPO</strong><br>
            Authorized Signatory
        </td>
    </tr>
</table>

<script>
    window.onload = function () {
        window.print();
    }
</script>

</body>
</html>