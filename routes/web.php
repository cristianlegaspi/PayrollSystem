<?php

use App\Models\Branch;
use Illuminate\Support\Facades\Route;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\PayslipService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DailyTimeRecord;
use App\Models\Employee;




// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/payroll/{payroll}/payslip', function (Payroll $payroll) {
    return PayslipService::generate($payroll);
})->name('payroll.payslip');

Route::get('/payroll/print/{period}/{branch?}', function (PayrollPeriod $period, $branchId = null) {
    
    // 1. Initialize Query
    $query = Payroll::with(['employee', 'employee.branch', 'contribution'])
        ->where('payroll_period_id', $period->id);

    // 2. Handle Branch Filtering (Only filter if an ID is actually passed)
    $branch = null;
    if ($branchId) {
        $branch = Branch::find($branchId);
        if ($branch) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $branch->id));
        }
    }

    // 3. Get Data
    $payrolls = $query->orderBy('employee_id')->get()->groupBy('employee.branch.branch_name');

    if ($payrolls->isEmpty()) {
        return "No records found for this period.";
    }

    // 4. Generate PDF
    $pdf = Pdf::loadView('reports.payroll-summary', [
        'period' => $period,
        'groupedPayrolls' => $payrolls, // Pass grouped data
        'branch' => $branch // Will be null if showing "All Branches"
    ])->setPaper('legal', 'landscape');

    $filename = $branch 
        ? "Payroll-{$branch->branch_name}-{$period->description}.pdf" 
        : "Payroll-AllBranches-{$period->description}.pdf";

    return $pdf->stream($filename);
})->name('payroll.print')->middleware(['auth']);

// DTR PDF route
// DTR PDF route
Route::get('/dtr/print', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    $branchId = $user->branch_id;
    $employeeId = $request->query('employee_id'); // optional
    $from = $request->query('from');             // optional YYYY-MM-DD
    $to = $request->query('to');                 // optional YYYY-MM-DD

    $query = DailyTimeRecord::with('employee');

    // Filter by branch (only non-admin users)
    if (!in_array($user->role?->role_name, ['Admin', 'Super Admin', 'Owner'])) {
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

    // Always generate PDF
    $pdf = Pdf::loadView('dtr.pdf', [
        'dtrs' => $dtrs,
        'noRecordsMessage' => $dtrs->isEmpty() ? "No DTR records found." : null
    ])->setPaper('a4', 'landscape');

    return $pdf->stream('Daily_Time_Records.pdf');
})->name('dtr.print')->middleware(['auth']);

Route::get('/dtr/pdf', function (\Illuminate\Http\Request $request) {
    $branchId = $request->query('branch_id');

    $query = Employee::query();
    if ($branchId && $branchId !== 'all') {
        $query->where('branch_id', $branchId);
    }

    $employees = $query->get();
    $branchName = $branchId === 'all' ? 'All Branches' : $employees->first()?->branch?->branch_name;

    // Generate PDF in landscape
    $pdf = Pdf::loadView('employees.dtr_pdf', [
        'employees' => $employees,
        'branch' => $branchName,
    ])->setPaper('a4', 'landscape'); // <-- landscape

    // Stream PDF to browser
    return $pdf->stream('DTR_Report.pdf');
})->name('employees.dtr.pdf')->middleware(['auth']);