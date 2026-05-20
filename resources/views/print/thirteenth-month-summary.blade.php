<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>13th Month Payroll Summary - {{ $year }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size:9px; color: #000; margin: 10px; }
        table { width:100%; border-collapse:collapse; margin-top: 10px; page-break-inside: auto; }
        tr { page-break-inside:avoid; page-break-after:auto; }
        th, td { border:1px solid #000; padding:4px; font-size: 8px; }
        th { text-align:center; font-weight:bold; background-color: #f2f2f2; uppercase; }
        td { text-align:right; }
        .text-left { text-align:left; }
        .bold { font-weight:bold; }
        .page-break { page-break-after: always; }
        .signature-line { border-top:1px solid #000; width:180px; margin-top:35px; }
        .no-border td { border:none; }
        .header-title { text-align:center; margin: 0 0 15px 0; }
    </style>
</head>
<body>

{{-- SECTION 1: GRAND SUMMARY SHEET PAGE --}}
<div class="page-break">
    <div class="header-title">
        <h3 style="margin:0; font-size:14px;">E.A OCAMPO ENTERPRISES</h3>
        <h4 style="margin:4px 0 0 0; font-size:11px;">GRAND 13TH MONTH PAYROLL SUMMARY - CALENDAR YEAR {{ $year }}</h4>
        <small style="color:#555;">Calculation Metric Basis: Total Basic Earned Divided By {{ $dividend }}</small>
    </div>
    
    <table>
        <thead>
            <tr>
                <th class="text-left">Branch Name Location</th>
                <th>Total Accumulated Basic Salary</th>
                <th>Grand Total 13th Month Pay Distribution</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grouped_data as $branchName => $employees)
                @php
                    $branchBasic = array_sum(array_column($employees, 'total_basic'));
                    $branchThirteenth = array_sum(array_column($employees, 'thirteenth_pay'));
                @endphp
                <tr>
                    <td class="text-left bold">{{ $branchName }}</td>
                    <td>₱{{ number_format($branchBasic, 2) }}</td>
                    <td class="bold">₱{{ number_format($branchThirteenth, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bold" style="background-color: #eaeded;">
                <td class="text-left">TOTAL (ALL BRANCHES)</td>
                <td>₱{{ number_format($grand_totals['basic'], 2) }}</td>
                <td>₱{{ number_format($grand_totals['thirteenth'], 2) }}</td>
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
</div>

{{-- SECTION 2: BREAKDOWN PER INDIVIDUAL BRANCH --}}
@foreach($grouped_data as $branchName => $employees)
<div class="{{ !$loop->last ? 'page-break' : '' }}">
    <div class="header-title">
        <h3 style="margin:0; font-size:13px;">E.A OCAMPO ENTERPRISES</h3>
        <h4 style="margin:3px 0 0 0; font-size:11px;">13th Month Pay Breakdown Summary — CY {{ $year }}</h4>
        <strong style="font-size:11px;">Branch: {{ $branchName }}</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-left" style="width:150px;">Employee Name</th>
                <th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th>
                <th>Jul</th><th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th>
                <th>Total Basic</th>
                <th>13th Month Pay (÷{{ $dividend }})</th>
                <th style="width:110px;">Signature Receipt</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $bBasicTotal = 0; 
                $b13thTotal = 0; 
            @endphp
            @foreach($employees as $emp)
                @php
                    $bBasicTotal += $emp['total_basic'];
                    $b13thTotal += $emp['thirteenth_pay'];
                @endphp
                <tr>
                    <td class="text-left bold">{{ $emp['name'] }}</td>
                    @for($m = 1; $m <= 12; $m++)
                        <td>{{ $emp['months'][$m] > 0 ? number_format($emp['months'][$m], 0) : '-' }}</td>
                    @endfor
                    <td class="bold">{{ number_format($emp['total_basic'], 2) }}</td>
                    <td class="bold" style="background-color: #fcfcfc;">₱{{ number_format($emp['thirteenth_pay'], 2) }}</td>
                    <td></td>
                </tr>
            @endforeach
            <tr class="bold" style="background-color: #f2f2f2;">
                <td class="text-left">TOTAL BRANCH SUMMARY</td>
                @for($m = 1; $m <= 12; $m++)
                    <td>{{ number_format(array_sum(array_map(fn($e) => $e['months'][$m], $employees)), 0) }}</td>
                @endfor
                <td>₱{{ number_format($bBasicTotal, 2) }}</td>
                <td>₱{{ number_format($b13thTotal, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
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
</div>
@endforeach

<script>
    // Automatically open the print dialog when the page loads
    window.onload = function() {
        window.print();
    }
</script>

</body>
</html>