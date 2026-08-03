<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;

class LeaveEmployeeReportController extends Controller
{
    public function __invoke(Request $request, Employee $employee)
    {
        $year = $request->get('year', now()->year);

        $applications = LeaveApplication::query()
            ->where('employee_id', $employee->id)
            ->whereYear('from_date', $year)
            ->orderBy('from_date')
            ->get();

        $balance = LeaveBalance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'year' => $year,
            ],
            [
                'annual_credit' => 5,
                'used_credit' => 0,
                'remaining_credit' => 5,
            ]
        );

        return view('reports.leave-employee', [
            'employee' => $employee,
            'applications' => $applications,
            'balance' => $balance,
            'year' => $year,
        ]);
    }
}