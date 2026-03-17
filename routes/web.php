<?php

use App\Models\Branch;
use Illuminate\Support\Facades\Route;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\PayslipService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DailyTimeRecord;



// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/payroll/{payroll}/payslip', function (Payroll $payroll) {
    return PayslipService::generate($payroll);
})->name('payroll.payslip');

Route::get('/payroll/print/{period}/{branch}', function (PayrollPeriod $period, Branch $branch) {
    
    $payrolls = Payroll::with(['employee', 'employee.branch', 'contribution'])
        ->where('payroll_period_id', $period->id)
        ->whereHas('employee', fn ($q) => $q->where('branch_id', $branch->id))
        ->orderBy('employee_id')
        ->get();

    if ($payrolls->isEmpty()) {
        return "No records found.";
    }

    $pdf = Pdf::loadView('reports.payroll-summary', [
        'period' => $period,
        'payrolls' => $payrolls,
        'branch' => $branch
    ])->setPaper('legal', 'landscape');

    // 'inline' ensures it opens in the browser viewer instead of forcing a download
    return $pdf->stream("Payroll-{$branch->branch_name}-{$period->description}.pdf");
})->name('payroll.print')->middleware(['auth']); // Ensure only logged-in users can access

// DTR PDF route
Route::get('/dtr/print', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    $branchId = $user->branch_id;
    $employeeId = $request->query('employee_id'); // optional
    $from = $request->query('from');             // optional YYYY-MM-DD
    $to = $request->query('to');                 // optional YYYY-MM-DD

    $query = DailyTimeRecord::with('employee');

    // Filter by branch (only non-admin users)
    if (!in_array($user->role?->role_name, ['Admin', 'Super Admin'])) {
        $query->whereHas('employee', fn($q) => $q->where('branch_id', $branchId));
    }

    // Optional filters
    if ($employeeId) {
        $query->where('employee_id', $employeeId);
    }
    if ($from) {
        $query->where('work_date', '>=', $from);
    }
    if ($to) {
        $query->where('work_date', '<=', $to);
    }

    // Get records
    $dtrs = $query->orderBy('work_date')->get();

    if ($dtrs->isEmpty()) {
        return "No DTR records found for your branch and selected criteria.";
    }

    $pdf = Pdf::loadView('dtr.pdf', ['dtrs' => $dtrs])
              ->setPaper('a4', 'landscape');

    return $pdf->stream('Daily_Time_Records.pdf');
})->name('dtr.print')->middleware(['auth']);