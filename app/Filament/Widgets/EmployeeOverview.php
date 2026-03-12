<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class EmployeeOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1; // Shows first on dashboard

    protected function getStats(): array
    {       
        $latestFinalizedPeriod = PayrollPeriod::where('status', 'finalized')
            ->orderByDesc('end_date')
            ->first();

        $periodDescription = 'No finalized payroll period';
        $totals = [
            'basic_salary' => 0, 
            'overtime_salary' => 0, 
            'night_diff_total' => 0, 
            'gross_pay' => 0, 
            'total_deductions' => 0, 
            'net_pay' => 0, 
            'cash_advance' => 0
        ];

        if ($latestFinalizedPeriod) {
            $startDate = Carbon::parse($latestFinalizedPeriod->start_date)->format('M d, Y');
            $endDate = Carbon::parse($latestFinalizedPeriod->end_date)->format('M d, Y');
            $periodDescription = "Period: $startDate - $endDate";

            $queryResults = Payroll::where('payroll_period_id', $latestFinalizedPeriod->id)
                ->selectRaw('
                    SUM(basic_salary) as basic_salary,
                    SUM(overtime_salary) as overtime_salary,
                    SUM(night_diff_salary + night_diff_ot_salary) as night_diff_total,
                    SUM(cash_advance) as cash_advance,
                    SUM(gross_pay) as gross_pay,
                    SUM(total_deductions) as total_deductions,
                    SUM(net_pay) as net_pay
                ')->first();

            if ($queryResults) {
                $totals = $queryResults->toArray();
            }
        }

        return [
            Stat::make('Total Employees', Employee::count())
                ->icon('heroicon-o-users')
                ->description('Active Workforce')
                ->color('info'),

            Stat::make('Net Payroll Cost', '₱' . number_format($totals['net_pay'], 2))
                ->description($periodDescription)
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Overtime & Night Diff', '₱' . number_format($totals['overtime_salary'] + $totals['night_diff_total'], 2))
                ->description('Total Premium Pay')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Total Deductions', '₱' . number_format($totals['total_deductions'], 2))
                ->description('Includes Cash Advances: ₱' . number_format($totals['cash_advance'], 2))
                ->icon('heroicon-o-arrow-trending-down')
                ->color('danger'),
        ];
    }
}