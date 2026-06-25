<?php

use App\Models\Branch;
use Illuminate\Support\Facades\Route;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\PayslipService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DailyTimeRecord;
use App\Models\Employee;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DTRExport;
use App\Imports\DTRImport;
use App\Http\Controllers\CashAdvancePrintController;



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
    
    $query = Payroll::with(['employee', 'employee.branch', 'contribution'])
        ->where('payroll_period_id', $period->id);

    $branch = null;
    if ($branchId) {
        $branch = Branch::find($branchId);
        if ($branch) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $branch->id));
        }
    }

    $payrolls = $query->orderBy('employee_id')->get()->groupBy('employee.branch.branch_name');

    if ($payrolls->isEmpty()) {
        return "No records found for this period.";
    }

    $pdf = Pdf::loadView('reports.payroll-summary', [
        'period' => $period,
        'groupedPayrolls' => $payrolls,
        'branch' => $branch
    ])->setPaper('legal', 'landscape');

    $filename = $branch 
        ? "Payroll-{$branch->branch_name}-{$period->description}.pdf" 
        : "Payroll-AllBranches-{$period->description}.pdf";

    return $pdf->stream($filename);
})->name('payroll.print')->middleware(['auth']);

Route::get('/dtr/print', function (Request $request) {
    $user = auth()->user();
    $employeeId = $request->query('employee_id');
    $from = $request->query('from');
    $to = $request->query('to');

    $query = DailyTimeRecord::with(['employee.branch', 'employee.position']);

    $roleName = $user->role?->role_name;
    if (!in_array($roleName, ['Admin', 'Super Admin', 'Owner'])) {
        $query->whereHas('employee', fn($q) => $q->where('branch_id', $user->branch_id));
    }

    if ($employeeId) {
        $query->where('employee_id', $employeeId);
    }
    if ($from) {
        $query->where('work_date', '>=', $from);
    }
    if ($to) {
        $query->where('work_date', '<=', $to);
    }

    $groupedDtrs = $query->orderBy('work_date', 'asc')->get()->groupBy('employee_id');

    if ($groupedDtrs->isEmpty()) {
        return "No DTR records found for the selected period.";
    }

    $pdf = Pdf::loadView('dtr.pdf', [
        'groupedDtrs' => $groupedDtrs,
        'from' => $from,
        'to' => $to
    ])->setPaper('a4', 'landscape');

    return $pdf->stream("DTR_Summary_{$from}_to_{$to}.pdf");
})->name('dtr.print')->middleware(['auth']);

Route::get('/dtr/export/excel', function (Request $request) {
    $user = auth()->user();
    $employeeId = $request->query('employee_id');
    $from = $request->query('from');
    $to = $request->query('to');

    return Excel::download(
        new DTRExport($employeeId, $from, $to, $user),
        "DTR_Summary_{$from}_to_{$to}.xlsx"
    );
})->name('dtr.export.excel')->middleware(['auth']);

Route::post('/dtr/import/excel', function (Request $request) {
    $request->validate([
        'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
    ]);

    Excel::import(new DTRImport(auth()->user()), $request->file('file'));

    return back()->with('success', 'DTR Excel file imported successfully.');
})->name('dtr.import.excel')->middleware(['auth']);

Route::get('/payroll/{period}/all-payslips/{branchIds?}', function (
    PayrollPeriod $period,
    $branchIds = null
) {

    $query = Payroll::with([
        'employee.branch',
        'employee.position',
        'contribution',
    ])
    ->where('payroll_period_id', $period->id);

    $selectedBranches = [];

    if (!empty($branchIds)) {

        $selectedBranches = explode(',', $branchIds);

        $query->whereHas('employee', function ($q) use ($selectedBranches) {

            $q->whereIn('branch_id', $selectedBranches);

        });
    }

    $payrolls = $query
        ->orderBy('employee_id')
        ->get();

    if ($payrolls->isEmpty()) {
        return "No payroll records found.";
    }

    $pdf = Pdf::loadView('reports.all-payslips', [
        'payrolls' => $payrolls,
        'period' => $period,
    ])->setPaper('a4');

    return $pdf->stream(
        "Payslips-{$period->description}.pdf"
    );

})->name('payroll.all-payslips')->middleware(['auth']);

Route::get('/admin/print-thirteenth-month-summary', function () {
    // Pull session flash context matrices 
    $printData = session()->get('thirteenth_month_print_data');

    if (! $printData) {
        return abort(403, 'Unauthorized access or session calculation expired. Please refresh table and retry.');
    }

    return view('print.thirteenth-month-summary', $printData);
})->name('thirteenth-month.print')->middleware(['web', 'auth']);


Route::middleware(['auth'])->group(function () {
    Route::get('/cash-advances/print/{employee}', [CashAdvancePrintController::class, 'show'])
        ->name('cash-advances.print');
});