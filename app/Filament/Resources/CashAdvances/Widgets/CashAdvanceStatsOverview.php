<?php

namespace App\Filament\Resources\CashAdvances\Widgets;

use App\Models\CashAdvance;
use App\Models\CashAdvancePayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CashAdvanceStatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $grandTotalCashAdvance = (float) CashAdvance::query()
            ->sum('amount');

        $grandTotalPaid = (float) CashAdvancePayment::query()
            ->sum('amount');

        $grandTotalBalance = $grandTotalCashAdvance - $grandTotalPaid;

        $employeeBalanceQuery = CashAdvance::query()
            ->select('employee_id')
            ->selectRaw('
                SUM(amount) - COALESCE(
                    (
                        SELECT SUM(cash_advance_payments.amount)
                        FROM cash_advance_payments
                        WHERE cash_advance_payments.employee_id = cash_advances.employee_id
                    ),
                    0
                ) as balance
            ')
            ->groupBy('employee_id')
            ->havingRaw('balance > 0');

        $totalEmployeesWithCA = DB::query()
            ->fromSub($employeeBalanceQuery, 'employee_ca_balances')
            ->count();

        return [
            Stat::make('Employees with C.A', number_format($totalEmployeesWithCA))
                ->description('Employees with outstanding cash advance')
                ->icon('heroicon-o-users')
                ->color('warning'),

            Stat::make('Grand Total Cash Advance', '₱ ' . number_format($grandTotalCashAdvance, 2))
                ->description('Total recorded cash advances')
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),

              Stat::make('Grand Total Balance', '₱ ' . number_format($grandTotalBalance, 2))
                ->description('Total remaining C.A balance')
                ->icon('heroicon-o-banknotes')
                ->color($grandTotalBalance > 0 ? 'danger' : 'success'),
        ];
    }
}