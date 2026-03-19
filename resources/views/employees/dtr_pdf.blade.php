<!DOCTYPE html>
<html>
<head>
    <title>Employee Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>E.A OCAMPO ENTERPRISES</h2>
        <h3>Summary of Employee Report</h3>
        <p>Branch: {{ $branch }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee Number</th>
                <th>Full Name</th>
                <th>Position</th>
                <th>Branch</th>
                <th>Employment Status</th>
                <th>Employment Type</th>
                <th>Daily Rate</th>
                <th>Date Hired</th>
                <th>Date of Birth</th>
                <th>TIN</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $employee)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $employee->employee_number }}</td>
                <td>{{ $employee->full_name }}</td>
                <td>{{ $employee->position_id }}</td>
                <td>{{ $employee->branch?->branch_name }}</td>
                <td>{{ $employee->employmentStatus?->name }}</td>
                <td>{{ $employee->employmentType?->name }}</td>
                <td>{{ number_format($employee->daily_rate, 2) }}</td>
                <td>{{ $employee->date_hired }}</td>
                <td>{{ $employee->date_of_birth }}</td>
                <td>{{ $employee->tin }}</td>
                <td>{{ $employee->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>