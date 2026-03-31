<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Summary</title>
    <style>
        body { font-family: Arial, sans-serif; font-size:10px; }
        table { width:100%; border-collapse:collapse; margin-top: 10px; }
        th, td { border:1px solid #000; padding:4px; }
        th { text-align:center; font-weight:bold; background-color: #f2f2f2; }
        td { text-align:right; }
        .text-left { text-align:left; }
        .bold { font-weight:bold; }
        .page-break { page-break-after: always; }
        .signature-line { border-top:1px solid #000; width:200px; margin-top:40px; }
        .no-border td { border:none; }
    </style>
</head>
<body>

{{-- GRAND SUMMARY PAGE --}}
<div class="page-break">
    <h3 style="text-align:center;margin-bottom:0;">E.A OCAMPO ENTERPRISES</h3>
    <h4 style="text-align:center;margin-top:5px;">GRAND PAYROLL SUMMARY - {{ $period->description }}</h4>
    
    <table>
        <thead>
            <tr>
                <th class="text-left">Branch</th>
                <th>Total Basic Salary</th>
                <th>Total Gross Pay</th>
                <th>SSS Salary Loan</th>
                <th>SSS Calamity Loan</th>
                <th>PagIBIG Salary Loan</th>
                <th>Total Cash Advance</th>
                <th>Total Shortages</th>
                <th>Total SSS (ER+EE)</th>
                <th>Total Premium SS</th>
                <th>Total PhilHealth (ER+EE)</th>
                <th>Total PagIBIG (ER+EE)</th>
                <th>Total Net Pay</th>
            </tr>
        </thead>
        <tbody>
            @php
                $overallTotal = [
                    'basic' => 0, 'gross' => 0, 'sss_loan' => 0, 'sss_cal' => 0, 'pi_loan' => 0,
                    'ca' => 0, 'short' => 0, 'sss' => 0, 'prem' => 0, 'ph' => 0, 'pi' => 0, 'net' => 0
                ];
                $startDay = \Carbon\Carbon::parse($period->start_date)->day;
                $isFirst = $startDay >= 1 && $startDay <= 15;
                $isSecond = $startDay >= 16;
            @endphp

            @foreach($groupedPayrolls as $branchName => $payrolls)
                @php
                    $bBasic = 0; $bGross = 0; $bSssL = 0; $bSssC = 0; $bPiL = 0;
                    $bCa = 0; $bShort = 0; $bSss = 0; $bPrem = 0; $bPh = 0; $bPi = 0; $bNet = 0;

                    foreach($payrolls as $p) {
                        $bBasic += $p->basic_salary ?? 0;
                        $bGross += $p->gross_pay ?? 0;
                        $bCa += $p->cash_advance ?? 0;
                        $bShort += $p->shortages ?? 0;

                        $sL = $isSecond ? ($p->contribution->sss_salary_loan ?? 0) : 0;
                        $sC = $isSecond ? ($p->contribution->sss_calamity_loan ?? 0) : 0;
                        $pL = $isSecond ? ($p->contribution->pagibig_salary_loan ?? 0) : 0;
                        $bSssL += $sL; $bSssC += $sC; $bPiL += $pL;

                        $sss_ee = $isFirst ? ($p->contribution->sss_ee ?? 0) : 0;
                        $sss_er = $isFirst ? ($p->contribution->sss_er ?? 0) : 0;
                        $bSss += ($sss_ee + $sss_er);

                        $pr = $isFirst ? ($p->contribution->premium_voluntary_ss_contribution ?? 0) : 0;
                        $bPrem += $pr;

                        $ph_ee = $isFirst ? ($p->contribution->philhealth_ee ?? 0) : 0;
                        $ph_er = $isFirst ? ($p->contribution->philhealth_er ?? 0) : 0;
                        $bPh += ($ph_ee + $ph_er);

                        $pi_ee = $isFirst ? ($p->contribution->pagibig_ee ?? 0) : 0;
                        $pi_er = $isFirst ? ($p->contribution->pagibig_er ?? 0) : 0;
                        $bPi += ($pi_ee + $pi_er);

                        $deductions = $sss_ee + $ph_ee + $pi_ee + $pr + $sL + $sC + $pL 
                                    + ($p->cash_advance ?? 0) + ($p->shortages ?? 0) + ($p->other_deduction ?? 0);
                        $bNet += (($p->gross_pay ?? 0) - $deductions);
                    }

                    $overallTotal['basic'] += $bBasic; $overallTotal['gross'] += $bGross;
                    $overallTotal['sss_loan'] += $bSssL; $overallTotal['sss_cal'] += $bSssC; $overallTotal['pi_loan'] += $bPiL;
                    $overallTotal['ca'] += $bCa; $overallTotal['short'] += $bShort;
                    $overallTotal['sss'] += $bSss; $overallTotal['prem'] += $bPrem;
                    $overallTotal['ph'] += $bPh; $overallTotal['pi'] += $bPi;
                    $overallTotal['net'] += $bNet;
                @endphp
                <tr>
                    <td class="text-left">{{ $branchName ?? 'No Branch' }}</td>
                    <td>{{ number_format($bBasic, 2) }}</td>
                    <td>{{ number_format($bGross, 2) }}</td>
                    <td>{{ number_format($bSssL, 2) }}</td>
                    <td>{{ number_format($bSssC, 2) }}</td>
                    <td>{{ number_format($bPiL, 2) }}</td>
                    <td>{{ number_format($bCa, 2) }}</td>
                    <td>{{ number_format($bShort, 2) }}</td>
                    <td>{{ number_format($bSss, 2) }}</td>
                    <td>{{ number_format($bPrem, 2) }}</td>
                    <td>{{ number_format($bPh, 2) }}</td>
                    <td>{{ number_format($bPi, 2) }}</td>
                    <td class="bold">{{ number_format($bNet, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bold">
                <td class="text-left">TOTAL (ALL BRANCHES)</td>
                <td>{{ number_format($overallTotal['basic'], 2) }}</td>
                <td>{{ number_format($overallTotal['gross'], 2) }}</td>
                <td>{{ number_format($overallTotal['sss_loan'], 2) }}</td>
                <td>{{ number_format($overallTotal['sss_cal'], 2) }}</td>
                <td>{{ number_format($overallTotal['pi_loan'], 2) }}</td>
                <td>{{ number_format($overallTotal['ca'], 2) }}</td>
                <td>{{ number_format($overallTotal['short'], 2) }}</td>
                <td>{{ number_format($overallTotal['sss'], 2) }}</td>
                <td>{{ number_format($overallTotal['prem'], 2) }}</td>
                <td>{{ number_format($overallTotal['ph'], 2) }}</td>
                <td>{{ number_format($overallTotal['pi'], 2) }}</td>
                <td style="background-color: #eee;">{{ number_format($overallTotal['net'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- INDIVIDUAL BRANCH DETAILS --}}
@foreach($groupedPayrolls as $branchName => $payrolls)
<div class="{{ !$loop->last ? 'page-break' : '' }}">
    <h3 style="text-align:center;margin-bottom:0;">E.A OCAMPO ENTERPRISES</h3>
    Payroll Summary - {{ $period->description }} <br>
    Branch: {{ $branchName ?? 'No Branch' }}

    @php
    $columns = [
        'days_worked', 'days_absent', 'undertime_hours', 'daily_rate', 'basic_salary',
        'overtime_salary', 'holiday_pay', 'gross_pay', 'cash_advance', 'shortages', 'other_deduction',
        'sss_er', 'sss_ee', 'premium_voluntary_ss_contribution', 'sss_salary_loan', 'sss_calamity_loan', 
        'philhealth_er', 'philhealth_ee', 'pagibig_er', 'pagibig_ee', 'pagibig_salary_loan', 
        'total_deductions', 'net_pay'
    ];
    $admin = array_fill_keys($columns, 0);
    $field = array_fill_keys($columns, 0);
    @endphp

    <table>
        <tr>
            <th class="text-left">Employee Name</th>
            @foreach(['Days Worked', 'Days Absent', 'UT Hours', 'Daily Rate', 'Basic Salary', 'Overtime Pay', 'Holiday Pay', 'Gross Pay', 'Cash Adv', 'Shortages', 'Other Ded', 'SSS ER', 'SSS EE', 'Prem SS', 'SSS Loan', 'SSS Cal', 'PH ER', 'PH EE', 'PAG ER', 'PAG EE', 'PAG Loan', 'Total Deduction', 'Net Pay', 'Signature'] as $header)
                <th>{{ $header }}</th>
            @endforeach
        </tr>

        @foreach($payrolls as $payroll)
        @php
            $type = $payroll->employee->employee_type ?? 'Field';
            $cat = strtolower($type) == 'admin' ? 'admin' : 'field';

            $startDay = \Carbon\Carbon::parse($period->start_date)->day;
            $isFirst = $startDay >= 1 && $startDay <= 15;
            $isSecond = $startDay >= 16;

            $sss_ee = $isFirst ? ($payroll->contribution->sss_ee ?? 0) : 0;
            $sss_er = $isFirst ? ($payroll->contribution->sss_er ?? 0) : 0;
            $ph_ee = $isFirst ? ($payroll->contribution->philhealth_ee ?? 0) : 0;
            $ph_er = $isFirst ? ($payroll->contribution->philhealth_er ?? 0) : 0;
            $pi_ee = $isFirst ? ($payroll->contribution->pagibig_ee ?? 0) : 0;
            $pi_er = $isFirst ? ($payroll->contribution->pagibig_er ?? 0) : 0;
            $prem = $isFirst ? ($payroll->contribution->premium_voluntary_ss_contribution ?? 0) : 0;

            $sss_loan = $isSecond ? ($payroll->contribution->sss_salary_loan ?? 0) : 0;
            $sss_cal = $isSecond ? ($payroll->contribution->sss_calamity_loan ?? 0) : 0;
            $pi_loan = $isSecond ? ($payroll->contribution->pagibig_salary_loan ?? 0) : 0;

            $cash = $payroll->cash_advance ?? 0;
            $short = $payroll->shortages ?? 0;
            $other = $payroll->other_deduction ?? 0;

            $totalOT = ($payroll->overtime_salary ?? 0) + ($payroll->sunday_ot_salary ?? 0) + ($payroll->rest_day_ot_salary ?? 0) + ($payroll->night_diff_salary ?? 0) + ($payroll->night_diff_ot_salary ?? 0);
            $holiday = $payroll->holiday_pay ?? 0;
            $totDed = $sss_ee + $ph_ee + $pi_ee + $prem + $sss_loan + $sss_cal + $pi_loan + $cash + $short + $other;
            $nP = ($payroll->gross_pay ?? 0) - $totDed;

            foreach($columns as $col) {
                if($col == 'overtime_salary') ${$cat}[$col] += $totalOT;
                elseif($col == 'holiday_pay') ${$cat}[$col] += $holiday;
                elseif($col == 'total_deductions') ${$cat}[$col] += $totDed;
                elseif($col == 'net_pay') ${$cat}[$col] += $nP;
                else ${$cat}[$col] += $payroll->$col ?? 0;
            }
        @endphp

        <tr>
            <td class="text-left">{{ $payroll->employee->full_name }}</td>
            <td>{{ $payroll->days_worked ?? 0 }}</td>
            <td>{{ $payroll->days_absent ?? 0 }}</td>
            <td>{{ number_format($payroll->undertime_hours ?? 0, 2) }}</td>
            <td>{{ number_format($payroll->daily_rate ?? 0, 2) }}</td>
            <td>{{ number_format($payroll->basic_salary ?? 0, 2) }}</td>
            <td>{{ number_format($totalOT, 2) }}</td>
            <td>{{ number_format($holiday, 2) }}</td>
            <td>{{ number_format($payroll->gross_pay ?? 0, 2) }}</td>
            <td>{{ number_format($cash, 2) }}</td>
            <td>{{ number_format($short, 2) }}</td>
            <td>{{ number_format($other, 2) }}</td>
            <td>{{ number_format($sss_er, 2) }}</td>
            <td>{{ number_format($sss_ee, 2) }}</td>
            <td>{{ number_format($prem, 2) }}</td>
            <td>{{ number_format($sss_loan, 2) }}</td>
            <td>{{ number_format($sss_cal, 2) }}</td>
            <td>{{ number_format($ph_er, 2) }}</td>
            <td>{{ number_format($ph_ee, 2) }}</td>
            <td>{{ number_format($pi_er, 2) }}</td>
            <td>{{ number_format($pi_ee, 2) }}</td>
            <td>{{ number_format($pi_loan, 2) }}</td>
            <td class="bold">{{ number_format($totDed, 2) }}</td>
            <td class="bold">{{ number_format($nP, 2) }}</td>
            <td class="signature"></td>
        </tr>
        @endforeach

        {{-- Totals Rows --}}
        <tr class="bold">
            <td class="text-left">TOTAL ADMIN</td>
            @foreach($columns as $col)
                <td>{{ number_format($admin[$col], 2) }}</td>
            @endforeach
            <td></td>
        </tr>
        <tr class="bold">
            <td class="text-left">TOTAL FIELD</td>
            @foreach($columns as $col)
                <td>{{ number_format($field[$col], 2) }}</td>
            @endforeach
            <td></td>
        </tr>
        <tr class="bold">
            <td class="text-left">TOTAL BRANCH</td>
            @foreach($columns as $col)
                <td>{{ number_format($admin[$col] + $field[$col], 2) }}</td>
            @endforeach
            <td></td>
        </tr>
    </table>

    <br><br>
    <table class="no-border">
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