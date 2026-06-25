<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\CashAdvancePayment;
use App\Models\Employee;

class CashAdvancePrintController extends Controller
{
    public function show(Employee $employee)
    {
        abort_unless(
            auth()->user()?->can('viewAny', CashAdvance::class),
            403
        );

        $employee->load(['branch', 'position']);

        $cashAdvances = CashAdvance::query()
            ->with(['payments'])
            ->where('employee_id', $employee->id)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $payments = CashAdvancePayment::query()
            ->with(['cashAdvance'])
            ->where('employee_id', $employee->id)
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        $totalCashAdvance = $cashAdvances->sum('amount');
        $totalPaid = $payments->sum('amount');
        $totalBalance = $totalCashAdvance - $totalPaid;

        return view('cash-advances.print', [
            'employee' => $employee,
            'cashAdvances' => $cashAdvances,
            'payments' => $payments,
            'totalCashAdvance' => $totalCashAdvance,
            'totalPaid' => $totalPaid,
            'totalBalance' => $totalBalance,
        ]);
    }
}