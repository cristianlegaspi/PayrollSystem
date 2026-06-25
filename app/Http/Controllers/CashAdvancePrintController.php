<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashAdvance;
use App\Models\CashAdvancePayment;
use App\Models\Employee;
use Illuminate\Http\Request;

class CashAdvancePrintController extends Controller
{
    public function print(Request $request)
    {
        abort_unless(
            auth()->user()?->can('viewAny', CashAdvance::class),
            403
        );

        $scope = $request->query('scope', 'employee');
        $branchId = $request->query('branch_id');
        $employeeId = $request->query('employee_id');

        $employeesQuery = Employee::query()
            ->with(['branch', 'position'])
            ->whereHas('cashAdvances')
            ->orderBy('full_name');

        $branch = null;

        if ($scope === 'employee' && $employeeId) {
            $employeesQuery->where('id', $employeeId);
        }

        if ($scope === 'branch' && $branchId && $branchId !== 'all') {
            $branch = Branch::find($branchId);
            $employeesQuery->where('branch_id', $branchId);
        }

        if ($scope === 'all') {
            $branch = null;
        }

        $employees = $employeesQuery->get();

        if ($employees->isEmpty()) {
            return 'No cash advance records found.';
        }

        $employeeIds = $employees->pluck('id');

        $cashAdvances = CashAdvance::query()
            ->with(['payments', 'employee.branch', 'employee.position'])
            ->whereIn('employee_id', $employeeIds)
            ->orderBy('employee_id')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get()
            ->groupBy('employee_id');

        $payments = CashAdvancePayment::query()
            ->with(['cashAdvance', 'employee.branch', 'employee.position'])
            ->whereIn('employee_id', $employeeIds)
            ->orderBy('employee_id')
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get()
            ->groupBy('employee_id');

        $summary = $employees->map(function ($employee) use ($cashAdvances, $payments) {
            $employeeCashAdvances = $cashAdvances->get($employee->id, collect());
            $employeePayments = $payments->get($employee->id, collect());

            $totalCashAdvance = (float) $employeeCashAdvances->sum('amount');
            $totalPaid = (float) $employeePayments->sum('amount');
            $totalBalance = $totalCashAdvance - $totalPaid;

            return [
                'employee' => $employee,
                'total_cash_advance' => $totalCashAdvance,
                'total_paid' => $totalPaid,
                'total_balance' => $totalBalance,
            ];
        });

        $grandTotalCashAdvance = $summary->sum('total_cash_advance');
        $grandTotalPaid = $summary->sum('total_paid');
        $grandTotalBalance = $summary->sum('total_balance');

        return view('cash-advances.print', [
            'scope' => $scope,
            'branch' => $branch,
            'employees' => $employees,
            'cashAdvances' => $cashAdvances,
            'payments' => $payments,
            'summary' => $summary,
            'grandTotalCashAdvance' => $grandTotalCashAdvance,
            'grandTotalPaid' => $grandTotalPaid,
            'grandTotalBalance' => $grandTotalBalance,
        ]);
    }
}