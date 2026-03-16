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
        // Get latest finalized payroll period
        $latestPeriod = PayrollPeriod::where('status', 'finalized')
            ->orderByDesc('end_date')
            ->first();

        if (!$latestPeriod) {
            return [Stat::make('Status', 'No finalized payroll found')];
        }

        $start = Carbon::parse($latestPeriod->start_date);
        $end = Carbon::parse($latestPeriod->end_date);
        $calendarDays = $start->diffInDays($end) + 1;

        // Aggregate payroll totals
        $totals = Payroll::where('payroll_period_id', $latestPeriod->id)
            ->selectRaw('
                SUM(basic_salary) as total_basic,
                SUM(overtime_salary) as total_ot,
                SUM(sunday_ot_salary) as total_sot,
                SUM(night_diff_salary + night_diff_ot_salary) as total_nd,
                SUM(gross_pay) as total_gross,
                SUM(cash_advance) as total_ca,
                SUM(shortages) as total_shortages,
                SUM(total_deductions) as total_deduct,
                SUM(other_deduction) as total_otherdeduct,
                
                SUM(net_pay) as total_net

                
            ')
            ->first();

        $periodLabel = $start->format('M d') . ' - ' . $end->format('M d, Y');

        // Combine OT + ND + ND OT for Total Overtime stat
        $totalOvertimeCombined = ($totals->total_ot ?? 0) + ($totals->total_nd ?? 0) + ($totals->total_sot ?? 0);

        $totaldeductionCombined = ($totals->total_deduct ?? 0) + ($totals->total_otherdeduct ?? 0);




        return [
            // 1. Period Duration
            Stat::make('Period Duration', "{$calendarDays} Days")
                ->description($periodLabel)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            // 2. Total Basic Salary
            Stat::make('Total Basic Salary', '₱' . number_format($totals->total_basic ?? 0, 2))
                ->description('Total base pay')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info'),

            // 3. Total Overtime (includes ND + ND OT)
            Stat::make('Total Overtime', '₱' . number_format($totalOvertimeCombined, 2))
                ->description('Extra hours rendered including night differential')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            // 4. Total Gross Pay
            Stat::make('Total Gross Pay', '₱' . number_format($totals->total_gross ?? 0, 2))
                ->description('Earnings before deductions')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->chart([$totals->total_basic ?? 0, $totals->total_gross ?? 0])
                ->color('info'),

            // 5. Cash Advance
            Stat::make('Total Cash Advance', '₱' . number_format($totals->total_ca ?? 0, 2))
                ->description('Outstanding CA')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            // 6. Shortages
            Stat::make('Total Shortages', '₱' . number_format($totals->total_shortages ?? 0, 2))
                ->description('Accountability deductions')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            // 7. Total Deductions
         Stat::make('Total Deductions', '₱' . number_format($totaldeductionCombined, 2))
                ->description('SSS, PH, PI & Loans, Other Deduction')
                ->descriptionIcon('heroicon-m-minus-circle')
                ->color('danger'),

            // 8. Total Net Pay
            Stat::make('Total Net Pay', '₱' . number_format($totals->total_net ?? 0, 2))
                ->description('Final payout amount')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([0, ($totals->total_net ?? 0) / 2, $totals->total_net ?? 0])
                ->color('success')
                ->extraAttributes(['class' => 'hover:scale-105 transition']),
        ];
    }
}