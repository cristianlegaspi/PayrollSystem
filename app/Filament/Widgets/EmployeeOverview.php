<?php

namespace App\Filament\Widgets;

use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class EmployeeOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $latestPeriod = PayrollPeriod::where('status', 'finalized')
            ->orderByDesc('end_date')
            ->first();

        if (!$latestPeriod) {
            return [Stat::make('Status', 'No finalized payroll found')];
        }

        $start = Carbon::parse($latestPeriod->start_date);
        $end = Carbon::parse($latestPeriod->end_date);
        $calendarDays = $start->diffInDays($end) + 1;

        $totals = Payroll::where('payroll_period_id', $latestPeriod->id)
            ->selectRaw('
                SUM(basic_salary) as total_basic,
                SUM(overtime_salary) as total_ot,
                SUM(night_diff_salary + night_diff_ot_salary) as total_nd,
                SUM(gross_pay) as total_gross,
                SUM(cash_advance) as total_ca,
                SUM(shortages) as total_shortages,
                SUM(total_deductions) as total_deduct,
                SUM(net_pay) as total_net
            ')->first();

        $periodLabel = $start->format('M d') . ' - ' . $end->format('M d, Y');

        return [
            // 1. Period Duration
            Stat::make('Period Duration', "{$calendarDays} Days")
                ->description($periodLabel)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            // 2. Basic Salary
            Stat::make('Total Basic Salary', '₱' . number_format($totals->total_basic ?? 0, 2))
                ->description('Total base pay')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info'),

            // 3. Total Overtime
            Stat::make('Total Overtime', '₱' . number_format($totals->total_ot ?? 0, 2))
                ->description('Extra hours rendered')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            // 4. NEW: Total Night Differential
            Stat::make('Total Night Diff', '₱' . number_format($totals->total_nd ?? 0, 2))
                ->description('Night shift premiums')
                ->descriptionIcon('heroicon-m-moon')
                ->color('info'),

            // 5. Total Gross Pay
            Stat::make('Total Gross Pay', '₱' . number_format($totals->total_gross ?? 0, 2))
                ->description('Earnings before deductions')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->chart([$totals->total_basic, $totals->total_gross])
                ->color('info'),

            // 6. Cash Advance
            Stat::make('Total Cash Advance', '₱' . number_format($totals->total_ca ?? 0, 2))
                ->description('Outstanding CA')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            // 7. Shortages
            Stat::make('Total Shortages', '₱' . number_format($totals->total_shortages ?? 0, 2))
                ->description('Accountability deductions')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            // 8. Total Deductions
            Stat::make('Total Deductions', '₱' . number_format($totals->total_deduct ?? 0, 2))
                ->description('SSS, PH, PI & Loans')
                ->descriptionIcon('heroicon-m-minus-circle')
                ->color('danger'),

            // 9. Total Net Pay
            Stat::make('Total Net Pay', '₱' . number_format($totals->total_net ?? 0, 2))
                ->description('Final payout amount')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([0, $totals->total_net / 2, $totals->total_net])
                ->color('success')
                ->extraAttributes(['class' => 'hover:scale-105 transition']),
        ];
    }
}