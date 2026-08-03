<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use Illuminate\Http\Request;

class LeaveSummaryReportController extends Controller
{
    public function __invoke(Request $request)
    {
        $applications = LeaveApplication::query()
            ->with([
                'employee.branch',
            ])
            ->orderBy('from_date')
            ->get();

        $totalApplications = $applications->count();

        $approved = $applications->where('status', 'Approved')->count();

        $pending = $applications->where('status', 'Pending')->count();

        $rejected = $applications->where('status', 'Rejected')->count();

        $totalLeaveDays = $applications->sum('days');

        return view('reports.leave-summary', [
            'applications' => $applications,
            'totalApplications' => $totalApplications,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'totalLeaveDays' => $totalLeaveDays,
        ]);
    }
}