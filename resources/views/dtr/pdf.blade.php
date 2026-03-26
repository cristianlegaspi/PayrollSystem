<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Time Records</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #f0f0f0; }
        .totals-table td, .totals-table th { border: 1px solid #000; padding: 6px; }
        .employee-header { margin-bottom: 20px; }
        .employee-header p { margin: 2px 0; }
        h3 { text-align: center; margin-bottom: 0px; }
        h1 { text-align: center; margin-bottom: 0px; }
        
        .no-dtr-message { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 70vh; 
            text-align: center; 
        }
        .no-dtr-message p { font-size: 16px; font-weight: bold; }
        
        .footer { 
            text-align: center; 
            font-size: 10px; 
            color: #555; 
            margin-top: 40px; 
            border-top: 1px solid #ccc; 
            padding-top: 5px; 
        }
    </style>
</head>
<body>

<h1>E.A OCAMPO ENTERPRISES</h1>
<h3>Daily Time Records (DTR)</h3>

@if($dtrs->isNotEmpty())

    @php
        $employee = $dtrs->first()?->employee;
        
        // 1. Total Regular Hours
        $lengthOfWork = $dtrs->sum('total_hours');
        
        // 2. FIXED: Day Work only counts if Regular Hours (total_hours) > 0
        // This excludes OT-only days (Sunday/Rest Day) from the count.
        $dayWork = $dtrs->where('total_hours', '>', 0)->count();
        
        // 3. Totals for OT and Undertime
        $totalUndertime = $dtrs->sum('undertime_hours');
        $totalOT = $dtrs->sum('overtime_hours');
        $totalRestDayOT = $dtrs->sum('rest_day_ot_hours');
        $totalSundayOT = $dtrs->sum('sunday_ot_hours');
        
        // 4. Absents
        $absent = $dtrs->where('status', 'absent_without_pay')->count();
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
                <th style="width: 85px;">Date</th>
                <th>Day</th>
                <th>Shift 1 In</th>
                <th>Shift 1 Out</th>
                <th>Shift 2 In</th>
                <th>Shift 2 Out</th>
                <th>Shift 3 In</th>
                <th>Shift 3 Out</th>
                <th>Reg Hrs</th>
                <th>Undertime</th>
                <th>OT</th>
                <th>RD OT</th>
                <th>Sun OT</th>
                <th style="width: 110px;">Remarks</th>
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
                <td>{{ number_format($dtr->total_hours, 2) }}</td>
                <td>{{ number_format($dtr->undertime_hours, 2) }}</td>
                <td>{{ number_format($dtr->overtime_hours, 2) }}</td>
                <td>{{ number_format($dtr->rest_day_ot_hours, 2) }}</td>
                <td>{{ number_format($dtr->sunday_ot_hours, 2) }}</td>
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
                <th>Rest Day OT</th>
                <th>Sunday OT</th>
                <th>Total Undertime (hrs)</th>
                <th>Absent</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($lengthOfWork, 2) }}</td>
                <td><strong>{{ $dayWork }}</strong></td>
                <td>{{ number_format($totalOT, 2) }}</td>
                <td>{{ number_format($totalRestDayOT, 2) }}</td>
                <td>{{ number_format($totalSundayOT, 2) }}</td>
                <td>{{ number_format($totalUndertime, 2) }}</td>
                <td>{{ $absent }}</td>
            </tr>
        </tbody>
    </table>

@else
    <div class="no-dtr-message">
        <p>No DTR records available for your branch and selected criteria.</p>
    </div>
@endif

<div class="footer">
    <p>This DTR is system generated and does not require a signature.</p>
</div>

</body>
</html>