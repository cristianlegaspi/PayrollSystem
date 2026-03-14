<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>

body{
    font-family: Arial;
    font-size:10px;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:8px;
}

td,th{
    border:1px solid #000;
    padding:5px;
}

.section{
    background:#eee;
    font-weight:bold;
}

.right{
    text-align:right;
}

.center{
    text-align:center;
}

.bold{
    font-weight:bold;
}

.no-border td{
    border:none;
}

.net{
    font-size:12px;
    font-weight:bold;
    background:#333;
    color:#fff;
}

</style>
</head>

<body>

<table class="no-border">
<tr>
<td class="center" style="font-size:16px;font-weight:bold;">
FULLTANK GAS STATION
</td>
</tr>

<tr>
<td class="center">
PAYROLL PERIOD: {{ $data['period'] }}
</td>
</tr>
</table>


<table>
<tr>
<td width="50%">
<strong>Name:</strong> {{ $data['employee_name'] }}
</td>

<td>
<strong>Daily Rate:</strong>
PHP {{ number_format($data['daily_rate'],2) }}
</td>
</tr>

<tr>
<td>
<strong>Position:</strong> {{ $data['position'] }}
</td>

<td>
<strong>Date:</strong> {{ $data['date_generated'] }}
</td>
</tr>
</table>


<table>

<tr class="section">
<td>Description</td>
<td class="right">Amount</td>
</tr>

<tr>
<td>Basic Salary ({{ $data['days_worked'] }} Days)</td>
<td class="right">PHP {{ number_format($data['basic_salary'],2) }}</td>
</tr>

<tr>
<td>Overtime Pay</td>
<td class="right">PHP {{ number_format($data['overtime_salary'],2) }}</td>
</tr>

<tr>
<td>Night Differential</td>
<td class="right">PHP {{ number_format($data['night_diff_salary'],2) }}</td>
</tr>

<tr>
<td>Night Differential OT</td>
<td class="right">PHP {{ number_format($data['night_diff_ot_salary'],2) }}</td>
</tr>

<tr class="bold">
<td>GROSS PAY</td>
<td class="right">PHP {{ number_format($data['gross_pay'],2) }}</td>
</tr>

</table>


<table>

<tr class="section">
<td colspan="2">DEDUCTIONS</td>
</tr>

<tr>
<td>SSS (EE Share)</td>
<td class="right">PHP {{ number_format($data['sss_ee'],2) }}</td>
</tr>

<tr>
<td>PhilHealth (EE Share)</td>
<td class="right">PHP {{ number_format($data['philhealth_ee'],2) }}</td>
</tr>

<tr>
<td>Pag-IBIG (EE Share)</td>
<td class="right">PHP {{ number_format($data['pagibig_ee'],2) }}</td>
</tr>

<tr>
<td>SSS Loan</td>
<td class="right">PHP {{ number_format($data['sss_loan'],2) }}</td>
</tr>

<tr>
<td>Pag-IBIG Loan</td>
<td class="right">PHP {{ number_format($data['pagibig_loan'],2) }}</td>
</tr>

<tr>
<td>Cash Advance</td>
<td class="right">PHP {{ number_format($data['cash_advance'],2) }}</td>
</tr>

<tr>
<td>Shortages</td>
<td class="right">PHP {{ number_format($data['shortages'],2) }}</td>
</tr>

<tr class="bold">
<td>TOTAL DEDUCTIONS</td>
<td class="right">PHP {{ number_format($data['total_deductions'],2) }}</td>
</tr>

</table>


<table>
<tr class="net">
<td>NET PAY</td>
<td class="right">
PHP {{ number_format($data['net_pay'],2) }}
</td>
</tr>
</table>


<br><br>

<table class="no-border">

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


</body>
</html>