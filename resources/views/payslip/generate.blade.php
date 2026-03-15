<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Payslip</title>
<style>
body {
    font-family: Arial, sans-serif;
    font-size: 10px;
    margin: 20px;
    color: #333;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
}

td, th {
    border: 1px solid #ccc;
    padding: 5px 8px;
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
}

.net {
    font-size: 12px;
    font-weight: bold;
    background: #007bff;
    color: #fff;
    padding: 10px;
}

.header {
    text-align: center;
    margin-bottom: 15px;
}

.header h1 {
    margin: 0;
    font-size: 18px;
    letter-spacing: 1px;
    color: #007bff;
}

.header p {
    margin: 2px 0 0;
    font-size: 12px;
    color: #555;
}

.earnings-table tr:nth-child(even),
.deductions-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.signature {
    margin-top: 30px;
}

.signature td {
    padding-top: 50px;
}

.footer {
    text-align: center;
    font-size: 9px;
    color: #777;
    margin-top: 20px;
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
<td>
<strong>Name:</strong> {{ $data['employee_name'] }}
</td>
<td>
<strong>Daily Rate:</strong> PHP {{ number_format($data['daily_rate'],2) }}
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

<table class="earnings-table">
<tr class="section">
<td>Description</td>
<td class="right">Amount</td>
</tr>
<tr>
<td>Basic Salary ({{ $data['days_worked'] }} Days)</td>
<td class="right">PHP {{ number_format($data['basic_salary'],2) }}</td>
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


</body>
</html>