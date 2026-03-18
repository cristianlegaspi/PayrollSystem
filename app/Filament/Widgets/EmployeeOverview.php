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

        // Fetch all payrolls with contributions
        $payrolls = Payroll::with('contribution')
            ->where('payroll_period_id', $latestPeriod->id)
            ->get();

        // Initialize totals
        $totals = [
            'total_basic' => 0,
            'total_ot' => 0,
            'total_nd' => 0,
            'total_nd_ot' => 0,
            'total_sunday_ot' => 0,
            'total_gross' => 0,
            'total_ca' => 0,
            'total_shortages' => 0,
            'total_other' => 0,
            'total_net' => 0,
            'total_sss_ee' => 0,
            'total_sss_er' => 0,
            'total_premium_ss' => 0,
            'total_sss_loan' => 0,
            'total_sss_calamity_loan' => 0,
            'total_philhealth_ee' => 0,
            'total_philhealth_er' => 0,
            'total_pagibig_ee' => 0,
            'total_pagibig_er' => 0,
            'total_pagibig_loan' => 0,
        ];

        foreach ($payrolls as $payroll) {
            $totals['total_basic'] += $payroll->basic_salary;
            $totals['total_ot'] += $payroll->overtime_salary;
            $totals['total_nd'] += $payroll->night_diff_salary;
            $totals['total_nd_ot'] += $payroll->night_diff_ot_salary;
            $totals['total_sunday_ot'] += $payroll->sunday_ot_salary;
            $totals['total_gross'] += $payroll->gross_pay;
            $totals['total_ca'] += $payroll->cash_advance;
            $totals['total_shortages'] += $payroll->shortages;
            $totals['total_other'] += $payroll->other_deduction;
            $totals['total_net'] += $payroll->net_pay;

            if ($payroll->contribution) {
                $totals['total_sss_ee'] += $payroll->contribution->sss_ee ?? 0;
                $totals['total_sss_er'] += $payroll->contribution->sss_er ?? 0;
                $totals['total_premium_ss'] += $payroll->contribution->premium_voluntary_ss_contribution ?? 0;
                $totals['total_sss_loan'] += $payroll->contribution->sss_salary_loan ?? 0;
                $totals['total_sss_calamity_loan'] += $payroll->contribution->sss_calamity_loan ?? 0;
                $totals['total_philhealth_ee'] += $payroll->contribution->philhealth_ee ?? 0;
                $totals['total_philhealth_er'] += $payroll->contribution->philhealth_er ?? 0;
                $totals['total_pagibig_ee'] += $payroll->contribution->pagibig_ee ?? 0;
                $totals['total_pagibig_er'] += $payroll->contribution->pagibig_er ?? 0;
                $totals['total_pagibig_loan'] += $payroll->contribution->pagibig_salary_loan ?? 0;
            }
        }

        $periodLabel = $start->format('M d') . ' - ' . $end->format('M d, Y');

        // Combine OT + ND + ND OT + Sunday OT for Total Overtime stat
        $totalOvertimeCombined = $totals['total_ot'] + $totals['total_nd'] + $totals['total_nd_ot'] + $totals['total_sunday_ot'];

        return [
            Stat::make('Period Duration', "{$calendarDays} Days")
                ->description($periodLabel)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Total Basic Salary', '₱' . number_format($totals['total_basic'], 2))
                ->description('Total base pay')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info'),

            Stat::make('Total Overtime Pay', '₱' . number_format($totalOvertimeCombined, 2))
                ->description('Includes OT, ND, ND OT, Sunday OT')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Total Gross Pay', '₱' . number_format($totals['total_gross'], 2))
                ->description('Earnings before deductions')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('info'),

            Stat::make('Total Cash Advance', '₱' . number_format($totals['total_ca'], 2))
                ->description('Outstanding cash advance')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            Stat::make('Total Shortages', '₱' . number_format($totals['total_shortages'], 2))
                ->description('Accountability deductions')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            // Stat::make('Total SSS EE', '₱' . number_format($totals['total_sss_ee'], 2))
            //     ->description('Employee SSS contribution')
            //     ->descriptionIcon('heroicon-m-user')
            //     ->color('danger'),

            Stat::make('Total SSS ER', '₱' . number_format($totals['total_sss_er'], 2))
                ->description('Employer SSS contribution')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),

            // Stat::make('Total Premium SS', '₱' . number_format($totals['total_premium_ss'], 2))
            //     ->description('Premium voluntary contribution')
            //     ->descriptionIcon('heroicon-m-star')
            //     ->color('info'),

            // Stat::make('Total SSS Loan', '₱' . number_format($totals['total_sss_loan'], 2))
            //     ->description('SSS salary loan deductions')
            //     ->descriptionIcon('heroicon-m-banknotes')
            //     ->color('danger'),

            // Stat::make('Total SSS Calamity Loan', '₱' . number_format($totals['total_sss_calamity_loan'], 2))
            //     ->description('SSS calamity loan deductions')
            //     ->descriptionIcon('heroicon-m-banknotes')
            //     ->color('danger'),

            // Stat::make('Total PhilHealth EE', '₱' . number_format($totals['total_philhealth_ee'], 2))
            //     ->description('Employee PhilHealth')
            //      ->descriptionIcon('heroicon-m-banknotes') // safe icon
            //     ->color('danger'),

            Stat::make('Total PhilHealth ER', '₱' . number_format($totals['total_philhealth_er'], 2))
                ->description('Employer PhilHealth')
                ->descriptionIcon('heroicon-m-banknotes') // safe icon
                ->color('success'),

            // Stat::make('Total PagIBIG EE', '₱' . number_format($totals['total_pagibig_ee'], 2))
            //     ->description('Employee PagIBIG')
            //     ->descriptionIcon('heroicon-m-home')
            //     ->color('danger'),

            Stat::make('Total PagIBIG ER', '₱' . number_format($totals['total_pagibig_er'], 2))
                ->description('Employer PagIBIG')
                ->descriptionIcon('heroicon-m-home')
                ->color('success'),

            // Stat::make('Total PagIBIG Loan', '₱' . number_format($totals['total_pagibig_loan'], 2))
            //     ->description('PagIBIG loan deductions')
            //     ->descriptionIcon('heroicon-m-banknotes')
            //     ->color('danger'),

            Stat::make('Total Net Pay', '₱' . number_format($totals['total_net'], 2))
                ->description('Final payout amount')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->extraAttributes(['class' => 'hover:scale-105 transition']),
        ];
    }
}