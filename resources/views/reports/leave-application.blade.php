<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Leave Application Form</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
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
            margin-top: 15px;
            background: #0B4D95;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            border-radius: 5px 5px 0 0;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .grid {
            border: 1px solid #d9d9d9;
        }

        .grid td,
        .grid th {
            border: 1px solid #d9d9d9;
            padding: 8px;
        }

        .grid th {
            background: #edf5fc;
            color: #0B4D95;
            font-size: 10px;
            text-transform: uppercase;
        }

        .label {
            background: #fafafa;
            font-weight: bold;
            width: 18%;
        }

        .value {
            font-weight: bold;
        }

        .remarks-box {
            border: 1px solid #d9d9d9;
            border-radius: 6px;
            overflow: hidden;
        }

        .remarks-header {
            background: #4CAF50;
            color: white;
            padding: 8px 10px;
            font-weight: bold;
        }

        .remarks-body {
            min-height: 120px;
            padding: 12px;
            line-height: 1.7;
        }

        .summary-table td {
            border: 1px solid #d9d9d9;
            padding: 9px;
            font-weight: bold;
        }

        .summary-label {
            background: #f7f7f7;
        }

        .summary-value {
            text-align: right;
        }

        .status-row {
            background: #0B4D95;
            color: white;
        }

        .signatures {
            margin-top: 55px;
        }

        .signature-line {
            width: 200px;
            border-top: 1px solid black;
            margin: auto;
            padding-top: 6px;
            text-align: center;
        }

        .footer {
            margin-top: 30px;
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
                    LEAVE APPLICATION
                </div>

                <div class="report-subtitle">
                    Human Resource Department
                </div>

            </td>

        </tr>

    </table>

</div>

<div class="section-title">
    Employee Information
</div>

<table class="grid">

    <tr>

        <td class="label">Employee</td>
        <td class="value">{{ $leave->employee->full_name }}</td>

        <td class="label">Branch</td>
        <td class="value">{{ $leave->employee->branch?->branch_name ?? '-' }}</td>

    </tr>

    <tr>

        <td class="label">Leave Type</td>
        <td class="value">{{ $leave->leave_type }}</td>

        <td class="label">Status</td>
        <td class="value">{{ $leave->status }}</td>

    </tr>

    <tr>

        <td class="label">Date Filed</td>
        <td class="value">{{ $leave->created_at->format('F d, Y') }}</td>

        <td class="label">Approved Date</td>
        <td class="value">
            {{ optional($leave->approved_date)->format('F d, Y h:i A') ?? '-' }}
        </td>

    </tr>

</table>

<div class="section-title">
    Leave Details
</div>

<table class="grid">

    <thead>

        <tr>

            <th>From Date</th>
            <th>To Date</th>
            <th>Number of Days</th>
            <th>Remaining Leave Credits</th>

        </tr>

    </thead>

    <tbody>

        <tr>

            <td align="center">
                {{ \Carbon\Carbon::parse($leave->from_date)->format('F d, Y') }}
            </td>

            <td align="center">
                {{ \Carbon\Carbon::parse($leave->to_date)->format('F d, Y') }}
            </td>

            <td align="center">
                {{ number_format($leave->days,1) }}
            </td>

            <td align="center">
                {{ optional($leave->employee->leaveBalance)->remaining ?? '5.0' }}
            </td>

        </tr>

    </tbody>

</table>

<table style="margin-top:15px;">

    <tr>

        <td style="width:65%;padding-right:12px;vertical-align:top;">

            <div class="remarks-box">

                <div class="remarks-header">
                    Reason for Leave
                </div>

                <div class="remarks-body">
                    {{ $leave->reason ?: 'No remarks provided.' }}
                </div>

            </div>

        </td>

        <td style="width:35%;vertical-align:top;">

            <table class="summary-table">

                <tr>

                    <td class="summary-label">
                        Leave Credits Used
                    </td>

                    <td class="summary-value">
                        {{ number_format($leave->days,1) }}
                    </td>

                </tr>

                <tr>

                    <td class="summary-label">
                        Remaining Credits
                    </td>

                    <td class="summary-value">
                        {{ optional($leave->employee->leaveBalance)->remaining ?? '5.0' }}
                    </td>

                </tr>

                <tr class="status-row">

                    <td>
                        Application Status
                    </td>

                    <td align="right">
                        {{ strtoupper($leave->status) }}
                    </td>

                </tr>

            </table>

        </td>

    </tr>

</table>

<table class="signatures">

    <tr>

        <td align="center">

            <div class="signature-line">
                Employee Signature
            </div>

        </td>

        <td align="center">

            <div class="signature-line">
                Immediate Supervisor
            </div>

        </td>

        <td align="center">

            <div class="signature-line">
                EDUARDO A. OCAMPO<br>
                Owner / Approving Authority
            </div>

        </td>

    </tr>

</table>

<div class="footer">

    <div>

        Generated on {{ now()->format('F d, Y h:i A') }}

    </div>

    <div>

        E.A. OCAMPO ENTERPRISES • Leave Application Form

    </div>

</div>

<script>
    window.onload = function () {
        window.print();
    };
</script>

</body>
</html>