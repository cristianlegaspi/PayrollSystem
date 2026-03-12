<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.3; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 4px; text-align: left; }
        th { background-color: #f0f0f0; }

        .section-title { background-color: #ddd; font-weight: bold; }
        .totals { font-weight: bold; background-color: #f7f7f7; }
        .net-pay { font-size: 12px; font-weight: bold; background-color: #ccc; }
        .right { text-align: right; }

        .company-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            border: none !important;
            padding: 8px 0;
        }

        .payroll-period {
            text-align: center;
            font-weight: bold;
            border: none !important;
            padding-bottom: 8px;
        }

        .no-border td { border: none !important; }
    </style>
</head>
<body>

@php
    // BASIC PAY CALCULATION
    $gross_basic = $data['daily_rate'] * $data['days_worked'];
    $undertime_rate_per_hour = $data['daily_rate'] / 8;
    $undertime = $data['undertime_hours'] * $undertime_rate_per_hour;
    $net_basic_pay = $gross_basic - $undertime; // removed absent deduction

    // ADDITIONAL PAY
    $net_holiday_pay = ($data['additions']['holiday_ot'] ?? 0) + ($data['additions']['other'] ?? 0);

    // GROSS PAY
    $gross_pay = $net_basic_pay + $net_holiday_pay;

    // DEDUCTIONS
    $net_deductions = array_sum($data['deductions']);

    // FINAL NET PAY
    $final_net_pay = $gross_pay - $net_deductions;
@endphp

<!-- HEADER -->
<table class="no-border">
    <tr>
        <td colspan="2" class="company-title">{{ $data['company'] }}</td>
    </tr>
    <tr>
        <td colspan="2" class="payroll-period">PAYROLL FOR PERIOD: {{ $data['payroll_period'] }}</td>
    </tr>
</table>

<table>
    <tr>
        <td><strong>Name:</strong> {{ $data['employee_name'] }}</td>
        <td><strong>Position:</strong> {{ $data['position'] }}</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Date:</strong> {{ $data['date'] }}</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Daily Rate:</strong> PHP {{ number_format($data['daily_rate'], 2) }}</td>
    </tr>
</table>

<!-- BASIC PAY -->
<table>
    <tr class="section-title">
        <td>Description</td>
        <td class="right">Value</td>
        <td class="right">Amount</td>
    </tr>

    <tr>
        <td>Days Worked</td>
        <td class="right">{{ number_format($data['days_worked'], 2) }} Days</td>
        <td class="right">PHP {{ number_format($gross_basic, 2) }}</td>
    </tr>

    <tr>
        <td>Undertime Hours</td>
        <td class="right">{{ number_format($data['undertime_hours'], 2) }} hrs × ({{ number_format($data['daily_rate'], 2) }} ÷ 8)</td>
        <td class="right">PHP ({{ number_format($undertime, 2) }})</td>
    </tr>

    <tr class="totals">
        <td>NET BASIC PAY</td>
        <td></td>
        <td class="right">PHP {{ number_format($net_basic_pay, 2) }}</td>
    </tr>
</table>

<!-- ADDITIONAL PAY -->
<table>
    <tr class="section-title">
        <td colspan="2">ADDITIONAL PAY</td>
    </tr>

    <tr>
        <td>Holiday / OT</td>
        <td class="right">PHP {{ number_format($data['additions']['holiday_ot'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Other Earnings</td>
        <td class="right">PHP {{ number_format($data['additions']['other'] ?? 0, 2) }}</td>
    </tr>

    <tr class="totals">
        <td>TOTAL ADDITIONAL PAY</td>
        <td class="right">PHP {{ number_format($net_holiday_pay, 2) }}</td>
    </tr>
</table>

<!-- GROSS PAY -->
<table>
    <tr class="totals">
        <td>GROSS PAY</td>
        <td class="right">PHP {{ number_format($gross_pay, 2) }}</td>
    </tr>
</table>

<!-- DEDUCTIONS -->
<table>
    <tr class="section-title">
        <td colspan="2">DEDUCTIONS</td>
    </tr>

    <tr>
        <td>SSS</td>
        <td class="right">PHP {{ number_format($data['deductions']['sss'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>PhilHealth</td>
        <td class="right">PHP {{ number_format($data['deductions']['philhealth'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Pag-IBIG</td>
        <td class="right">PHP {{ number_format($data['deductions']['pagibig'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Loans</td>
        <td class="right">PHP {{ number_format($data['deductions']['loan'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Shortages</td>
        <td class="right">PHP {{ number_format($data['deductions']['shortages'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>Cash Advance</td>
        <td class="right">PHP {{ number_format($data['deductions']['advances'] ?? 0, 2) }}</td>
    </tr>

    <tr class="totals">
        <td>TOTAL DEDUCTIONS</td>
        <td class="right">
            PHP {{ number_format(
                ($data['deductions']['sss'] ?? 0)
                + ($data['deductions']['philhealth'] ?? 0)
                + ($data['deductions']['pagibig'] ?? 0)
                + ($data['deductions']['loan'] ?? 0)
                + ($data['deductions']['shortages'] ?? 0)
                + ($data['deductions']['advances'] ?? 0),
            2) }}
        </td>
    </tr>
</table>

<!-- NET PAY -->
<table>
    <tr class="net-pay">
        <td>NET PAY FOR THE PERIOD</td>
        <td class="right">PHP {{ number_format($final_net_pay, 2) }}</td>
    </tr>
</table>

<!-- FOOTER -->
<table style="margin-top:30px;">
    <tr>
        <td style="width:50%; text-align:center;">
            _______________________________<br>
            <strong>Employer's Signature</strong>
        </td>
        <td style="width:50%; text-align:center;">
            {{ now()->format('M/d/Y') }}<br>
            <strong>Date Generated</strong>
        </td>
    </tr>
</table>

</body>
</html>