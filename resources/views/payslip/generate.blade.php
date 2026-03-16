<!DOCTYPE html>
<html>
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

<body>

<div class="header">
    <h1>FULLTANK GAS STATION</h1>
    <p>PAYROLL PERIOD: {{ $data['period'] }}</p>
</div>

<table class="no-border">
<tr>
<td><strong>Name:</strong> {{ $data['employee_name'] }}</td>
<td><strong>Daily Rate:</strong> PHP {{ number_format($data['daily_rate'],2) }}</td>
</tr>
<tr>
<td><strong>Position:</strong> {{ $data['position'] }}</td>
<td><strong>Date:</strong> {{ $data['date_generated'] }}</td>
</tr>
</table>

<table class="earnings-table">
<tr class="section">
<td>Description</td>
<td class="right">Amount</td>
</tr>

<tr>
<td>Basic Salary ({{ $data['days_worked'] }} Day)</td>
<td class="right">PHP {{ number_format($data['basic_salary'],2) }}</td>
</tr>

<tr>
<td>
Undertime Deduction 
({{ number_format($data['daily_rate'] / 8,2) }} × {{ $data['undertime_hours'] }} hrs)
</td>
<td class="right">PHP {{ number_format($data['undertime_deduction'],2) }}</td>
</tr>

<tr>
<td>Regular Overtime Pay (Mon to Sat)</td>
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

<tr>
<td>Sunday OT</td>
<td class="right">PHP {{ number_format($data['sunday_ot_salary'],2) }}</td>
</tr>

<tr class="bold">
<td>GROSS PAY</td>
<td class="right">PHP {{ number_format($data['gross_pay'],2) }}</td>
</tr>
</table>

<table class="deductions-table">
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

<tr>
<td>Other Deduction</td>
<td class="right">PHP {{ number_format($data['other_deduction'],2) }}</td>
</tr>

<tr class="bold">
<td>TOTAL DEDUCTIONS</td>
<td class="right">PHP {{ number_format($data['total_deductions'],2) }}</td>
</tr>
</table>

<table>
<tr class="net">
<td>NET PAY</td>
<td class="right">PHP {{ number_format($data['net_pay'],2) }}</td>
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