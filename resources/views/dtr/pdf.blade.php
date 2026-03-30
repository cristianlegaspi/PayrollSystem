<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Time Records Summary</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; }
        .page { padding: 20px; position: relative; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-left { text-align: left; }
        .page-break { page-break-after: always; }
        h2, h3 { text-align: center; margin: 2px 0; }
        .header-info { margin-bottom: 10px; width: 100%; }
        .footer { text-align: center; font-size: 9px; margin-top: 20px; border-top: 1px solid #ccc; }
    </style>
</head>
<body>

@foreach($groupedDtrs as $employeeId => $dtrs)
    @php
        $employee = $dtrs->first()->employee;
        $totalHours = $dtrs->sum('total_hours');
        $dayWork = $dtrs->where('total_hours', '>', 0)->count();
        $totalUndertime = $dtrs->sum('undertime_hours');
        $totalOT = $dtrs->sum('overtime_hours');
        $totalRestDayOT = $dtrs->sum('rest_day_ot_hours');
        $totalSundayOT = $dtrs->sum('sunday_ot_hours');
        $absent = $dtrs->where('status', 'absent_without_pay')->count();
    @endphp

    <div class="page {{ !$loop->last ? 'page-break' : '' }}">
        <h2>E.A OCAMPO ENTERPRISES</h2>
        <h3>Daily Time Record (DTR)</h3>
        <p style="text-align:center;">Period: {{ $from }} to {{ $to }}</p>

        <table style="border:none; margin-bottom: 15px;">
            <tr style="border:none;">
                <td style="border:none; text-align:left;">
                    <strong>Name:</strong> {{ $employee->full_name }} <br>
                    <strong>Position:</strong> {{ $employee->position->position_name ?? 'N/A' }}
                </td>
                <td style="border:none; text-align:right;">
                    <strong>Branch:</strong> {{ $employee->branch->branch_name ?? 'N/A' }} <br>
                    <strong>Rate:</strong> PHP {{ number_format($employee->daily_rate, 2) }}
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>S1 In</th>
                    <th>S1 Out</th>
                    <th>S2 In</th>
                    <th>S2 Out</th>
                    <th>S3 In</th>
                    <th>S3 Out</th>
                    <th>Reg Hrs</th>
                    <th>UT</th>
                    <th>OT</th>
                    <th>RD OT</th>
                    <th>Sun OT</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dtrs as $dtr)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($dtr->work_date)->format('M d') }}</td>
                    <td>{{ \Carbon\Carbon::parse($dtr->work_date)->format('D') }}</td>
                    <td>{{ $dtr->shift1_time_in ? \Carbon\Carbon::parse($dtr->shift1_time_in)->format('h:i A') : '-' }}</td>
                    <td>{{ $dtr->shift1_time_out ? \Carbon\Carbon::parse($dtr->shift1_time_out)->format('h:i A') : '-' }}</td>
                    <td>{{ $dtr->shift2_time_in ? \Carbon\Carbon::parse($dtr->shift2_time_in)->format('h:i A') : '-' }}</td>
                    <td>{{ $dtr->shift2_time_out ? \Carbon\Carbon::parse($dtr->shift2_time_out)->format('h:i A') : '-' }}</td>
                    <td>{{ $dtr->shift3_time_in ? \Carbon\Carbon::parse($dtr->shift3_time_in)->format('h:i A') : '-' }}</td>
                    <td>{{ $dtr->shift3_time_out ? \Carbon\Carbon::parse($dtr->shift3_time_out)->format('h:i A') : '-' }}</td>
                    <td>{{ number_format($dtr->total_hours, 2) }}</td>
                    <td>{{ number_format($dtr->undertime_hours, 2) }}</td>
                    <td>{{ number_format($dtr->overtime_hours, 2) }}</td>
                    <td>{{ number_format($dtr->rest_day_ot_hours, 2) }}</td>
                    <td>{{ number_format($dtr->sunday_ot_hours, 2) }}</td>
                    <td><small>{{ $dtr->remarks }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table style="width: 100%; border: 1px solid #000;">
            <tr style="background-color: #eee; font-weight: bold;">
                <td>Total Regular Hours</td>
                <td>Days Worked</td>
                <td>Total OT</td>
                <td>Total Undertime</td>
                <td>Absents</td>
            </tr>
            <tr>
                <td>{{ number_format($totalHours, 2) }}</td>
                <td>{{ $dayWork }}</td>
                <td>{{ number_format($totalOT + $totalRestDayOT + $totalSundayOT, 2) }}</td>
                <td>{{ number_format($totalUndertime, 2) }}</td>
                <td>{{ $absent }}</td>
            </tr>
        </table>

        <div style="margin-top: 30px;">
            <div style="float:left; width: 200px; border-top: 1px solid #000; text-align:center;">Employee Signature</div>
            <div style="float:right; width: 200px; border-top: 1px solid #000; text-align:center;">Authorized Signature</div>
        </div>
    </div>
@endforeach

</body>
</html>