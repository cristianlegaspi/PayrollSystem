<?php

use App\Models\Branch;
use Illuminate\Support\Facades\Route;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\PayslipService;
use Barryvdh\DomPDF\Facade\Pdf;

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

