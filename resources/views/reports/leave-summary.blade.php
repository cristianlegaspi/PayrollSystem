<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Leave Application Summary Report</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header {
            border: 1px solid #d9d9d9;
            border-left: 6px solid #0B4D95;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 15px;
        }

        .company {
            font-size: 20px;
            font-weight: bold;
            color: #0B4D95;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .report-title {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #0B4D95;
        }

        .report-subtitle {
            text-align: right;
            color: #666;
            font-size: 10px;
        }

        .section-title {
            margin-top: 10px;
            background: #0B4D95;
            color: #fff;
            padding: 8px 12px;
            font-weight: bold;
            border-radius: 5px 5px 0 0;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .grid {
            border: 1px solid #d9d9d9;
        }

        .grid th,
        .grid td {
            border: 1px solid #d9d9d9;
            padding: 8px;
        }

        .grid th {
            background: #EDF5FC;
            color: #0B4D95;
            font-size: 10px;
            text-transform: uppercase;
            text-align: center;
        }

        .grid td {
            font-size: 10px;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .summary-box {
            margin-top: 15px;
        }

        .summary-table td {
            border: 1px solid #d9d9d9;
            padding: 10px;
            font-weight: bold;
        }

        .summary-label {
            background: #f8f8f8;
            width: 25%;
        }

        .summary-value {
            text-align: right;
        }

        .signature-table {
            margin-top: 60px;
        }

        .signature-line {
            width: 220px;
            border-top: 1px solid #000;
            margin: auto;
            padding-top: 8px;
            text-align: center;
        }

        .footer {
            margin-top: 25px;
            border-top: 3px solid #4CAF50;
            padding-top: 6px;
            font-size: 9px;
            color: #666;
            display: flex;
            justify-content: space-between;
        }
    </style>

</head>

<body>

<div class="header">

<table>

<tr>

<td>

<div class="company">
E.A. OCAMPO ENTERPRISES
</div>

</td>

<td style="text-align:right;">

<div class="report-title">
LEAVE APPLICATION SUMMARY
</div>

<div class="report-subtitle">
Human Resource Department
</div>

<div class="report-subtitle">
Generated {{ now()->format('F d, Y h:i A') }}
</div>

</td>

</tr>

</table>

</div>

<div class="section-title">
Leave Application Records
</div>

<table class="grid">

<thead>

<tr>

<th width="10%">Branch</th>
<th width="16%">Employee</th>
<th width="10%">From</th>
<th width="10%">To</th>
<th width="6%">Days</th>
<th width="10%">Leave Type</th>
<th>Reason</th>
<th width="8%">Status</th>
<th width="12%">Approved Date</th>

</tr>

</thead>

<tbody>

@forelse($applications as $leave)

<tr>

<td class="text-left">
{{ $leave->employee?->branch?->branch_name ?? '-' }}
</td>

<td class="text-left">
{{ $leave->employee?->full_name }}
</td>

<td class="text-center">
{{ optional($leave->from_date)->format('M d, Y') }}
</td>

<td class="text-center">
{{ optional($leave->to_date)->format('M d, Y') }}
</td>

<td class="text-center">
{{ number_format($leave->days,1) }}
</td>

<td class="text-center">
{{ $leave->leave_type }}
</td>

<td class="text-left">
{{ $leave->reason }}
</td>

<td class="text-center">
{{ strtoupper($leave->status) }}
</td>

<td class="text-center">
{{ optional($leave->approved_date)->format('M d, Y') ?? '-' }}
</td>

</tr>

@empty

<tr>

<td colspan="9" class="text-center">
No leave applications found.
</td>

</tr>

@endforelse

</tbody>

</table>

<div class="summary-box">

<div class="section-title">
Summary
</div>

<table class="summary-table">

<tr>

<td class="summary-label">
Total Applications
</td>

<td class="summary-value">
{{ $totalApplications }}
</td>

<td class="summary-label">
Approved
</td>

<td class="summary-value">
{{ $approved }}
</td>

</tr>

<tr>

<td class="summary-label">
Pending
</td>

<td class="summary-value">
{{ $pending }}
</td>

<td class="summary-label">
Rejected
</td>

<td class="summary-value">
{{ $rejected }}
</td>

</tr>

<tr>

<td class="summary-label">
Total Leave Days
</td>

<td class="summary-value">
{{ number_format($totalLeaveDays,1) }}
</td>

<td colspan="2"></td>

</tr>

</table>

</div>

<table class="signature-table">

<tr>

<td align="center">

<div class="signature-line">

Prepared By

<br><br>

Assigned Secretary

</div>

</td>

<td align="center">

<div class="signature-line">

Checked By

<br><br>

Payroll Officer

</div>

</td>

<td align="center">

<div class="signature-line">

Approved By

<br><br>

<strong>EDUARDO A. OCAMPO</strong>

</div>

</td>

</tr>

</table>

<div class="footer">

<div>

Generated by Payroll & Human Resource Management System

</div>

<div>

Printed on {{ now()->format('F d, Y h:i A') }}

</div>

</div>

<script>
window.onload = function () {
    window.print();
};
</script>

</body>

</html>