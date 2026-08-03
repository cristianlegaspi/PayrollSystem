<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employee Leave Ledger</title>

    <style>
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size:12px;
            margin:30px;
            color:#000;
        }

        h2,h3,p{
            margin:0;
        }

        .text-center{
            text-align:center;
        }

        .mt-20{
            margin-top:20px;
        }

        .mb-20{
            margin-bottom:20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        table th,
        table td{
            border:1px solid #000;
            padding:6px;
            font-size:11px;
        }

        table th{
            background:#efefef;
        }

        .no-border td{
            border:none;
            padding:4px;
        }

        .right{
            text-align:right;
        }

        .center{
            text-align:center;
        }

        .signature{
            margin-top:60px;
        }

        .signature td{
            border:none;
            text-align:center;
            padding-top:40px;
        }

        .line{
            border-top:1px solid #000;
            width:220px;
            margin:auto;
            padding-top:5px;
        }

        @media print{

            .no-print{
                display:none;
            }

            body{
                margin:10px;
            }

        }
    </style>

</head>

<body>

<div class="no-print" style="margin-bottom:15px;">
    <button onclick="window.print()">Print</button>
</div>

<div class="text-center">

    <h2>YOUR COMPANY NAME</h2>
    <h3>EMPLOYEE LEAVE LEDGER</h3>

    <p>
        Calendar Year {{ $year }}
    </p>

</div>

<table class="no-border mt-20">

    <tr>
        <td width="20%"><strong>Employee</strong></td>
        <td width="30%">{{ $employee->full_name }}</td>

        <td width="20%"><strong>Branch</strong></td>
        <td>{{ $employee->branch?->branch_name }}</td>
    </tr>

    <tr>
        <td><strong>Annual Credit</strong></td>
        <td>{{ number_format($balance->annual_credit,1) }}</td>

        <td><strong>Used Credit</strong></td>
        <td>{{ number_format($balance->used_credit,1) }}</td>
    </tr>

    <tr>
        <td><strong>Remaining Credit</strong></td>
        <td>{{ number_format($balance->remaining_credit,1) }}</td>

        <td><strong>Printed On</strong></td>
        <td>{{ now()->format('F d, Y h:i A') }}</td>
    </tr>

</table>

<h3 class="mt-20">Leave History</h3>

<table class="mt-20">

    <thead>

    <tr>

        <th width="12%">Date Filed</th>
        <th width="12%">From</th>
        <th width="12%">To</th>
        <th width="8%">Days</th>
        <th width="15%">Leave Type</th>
        <th>Reason</th>
        <th width="10%">Status</th>
        <th width="15%">Approved Date</th>

    </tr>

    </thead>

    <tbody>

    @forelse($applications as $leave)

        <tr>

            <td class="center">
                {{ optional($leave->created_at)->format('M d, Y') }}
            </td>

            <td class="center">
                {{ optional($leave->from_date)->format('M d, Y') }}
            </td>

            <td class="center">
                {{ optional($leave->to_date)->format('M d, Y') }}
            </td>

            <td class="center">
                {{ number_format($leave->days,1) }}
            </td>

            <td>
                {{ $leave->leave_type }}
            </td>

            <td>
                {{ $leave->reason }}
            </td>

            <td class="center">
                {{ $leave->status }}
            </td>

            <td class="center">
                {{ optional($leave->approved_date)->format('M d, Y') ?? '-' }}
            </td>

        </tr>

    @empty

        <tr>

            <td colspan="8" class="center">
                No leave history found.
            </td>

        </tr>

    @endforelse

    </tbody>

</table>

<table class="signature">

    <tr>

        <td>
            <div class="line">
                Prepared By
            </div>
        </td>

        <td>
            <div class="line">
                Checked By
            </div>
        </td>

        <td>
            <div class="line">
                Approved By
            </div>
        </td>

    </tr>

</table>

<script>
    window.onload = function () {
        window.print();
    };
</script>

</body>

</html>