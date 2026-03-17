<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Time Records</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #f0f0f0; }
        .totals-table td, .totals-table th { border: 1px solid #000; padding: 6px; }
        .certification { margin-top: 40px; font-size: 12px; line-height: 1.5; }
        .signature { margin-top: 60px; text-align: left; }
        .employee-header { margin-bottom: 20px; }
        .employee-header p { margin: 2px 0; }
        h3 { text-align: center; margin-bottom: 0px; }
          h1 { text-align: center; margin-bottom: 0px; }
    </style>
</head>
<body>

<h1>E.A OCAMPO ENTERPRISES</h1>
<h3>Daily Time Records (DTR)</h3>

@if($dtrs->isNotEmpty())

    @php
        $employee = $dtrs->first()?->employee;
        $lengthOfWork = $dtrs->sum('total_hours');
        $dayWork = $dtrs->count();
        $totalUndertime = $dtrs->sum('undertime_hours');
        $totalOT = $dtrs->sum('overtime_hours');
        $absent = $dtrs->where('status', 'absent_without_pay')->count();
        $restDay = $dtrs->where('status', 'rest_day')->count();
    @endphp

    {{-- Employee Header --}}
    <div class="employee-header">
        <p><strong>Employee Name:</strong> {{ $employee->full_name ?? 'N/A' }}</p>
        <p><strong>Position:</strong> {{ $employee->position->position_name ?? 'N/A' }}</p>
        <p><strong>Daily Rate:</strong> PHP {{ number_format($employee->daily_rate ?? 0, 2) }}</p>
        <p><strong>Branch:</strong> {{ $employee->branch->branch_name ?? 'N/A' }}</p>
    </div>

    {{-- DTR Table --}}
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Day</th>
                <th>Shift 1 In</th>
                <th>Shift 1 Out</th>
                <th>Shift 2 In</th>
                <th>Shift 2 Out</th>
                <th>Shift 3 In</th>
                <th>Shift 3 Out</th>
                <th>Length of Work</th>
                <th>Undertime</th>
                <th>OT</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dtrs as $dtr)
            <tr>
                <td>{{ \Carbon\Carbon::parse($dtr->work_date)->format('M d, Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($dtr->work_date)->format('l') }}</td>
                <td>{{ $dtr->shift1_time_in ? \Carbon\Carbon::parse($dtr->shift1_time_in)->format('h:i A') : '-' }}</td>
                <td>{{ $dtr->shift1_time_out ? \Carbon\Carbon::parse($dtr->shift1_time_out)->format('h:i A') : '-' }}</td>
                <td>{{ $dtr->shift2_time_in ? \Carbon\Carbon::parse($dtr->shift2_time_in)->format('h:i A') : '-' }}</td>
                <td>{{ $dtr->shift2_time_out ? \Carbon\Carbon::parse($dtr->shift2_time_out)->format('h:i A') : '-' }}</td>
                <td>{{ $dtr->shift3_time_in ? \Carbon\Carbon::parse($dtr->shift3_time_in)->format('h:i A') : '-' }}</td>
                <td>{{ $dtr->shift3_time_out ? \Carbon\Carbon::parse($dtr->shift3_time_out)->format('h:i A') : '-' }}</td>
                <td>{{ $dtr->total_hours }}</td>
                <td>{{ $dtr->undertime_hours }}</td>
                <td>{{ $dtr->overtime_hours }}</td>
                <td>{{ $dtr->remarks ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals Table --}}
    <table class="totals-table">
        <thead>
            <tr>
                <th>Length of Work (hrs)</th>
                <th>Day Work</th>
                <th>Total OT Hours</th>
                <th>Total Undertime (hrs)</th>
                <th>Absent</th>
                <th>Rest Day</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $lengthOfWork }}</td>
                <td>{{ $dayWork }}</td>
                <td>{{ $totalOT }}</td>
                <td>{{ $totalUndertime }}</td>
                <td>{{ $absent }}</td>
                <td>{{ $restDay }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Certification --}}
    {{-- <div class="certification">
        I certify on my honor that the above is a true and correct report of the hours of work performed, 
        record of which was made daily at the time of arrival at and departure from office.
    </div> --}}

    {{-- Signature --}}
    {{-- <div class="signature">
        <p>Name: ___________________________</p>
        <p>Signature: _______________________</p>
    </div> --}}

@else
    <p>No DTR records available.</p>
@endif

</body>
</html>