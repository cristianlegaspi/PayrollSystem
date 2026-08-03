<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;

class LeaveApplicationPrintController extends Controller
{
    public function __invoke(LeaveApplication $leaveApplication)
    {
        return view('reports.leave-application', [
            'leave' => $leaveApplication->load([
                'employee.branch',
            ]),
        ]);
    }
}