<?php

namespace App\Filament\Widgets;

use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class EmployeeOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Filament::auth()->user();
        $roleName = $user->role?->role_name;

        $allowedAllBranches = ['Admin', 'Super Admin', 'Owner'];

        $pendingApprovalsCount = PayrollPeriod::where('status', 'Finalized')
            ->where('remarks', 'Pending')
            ->count();

        $latestPeriod = PayrollPeriod::where('status', 'Finalized')
            ->orderByDesc('end_date')
            ->first();

        $stats = [
            Stat::make('For Approval', $pendingApprovalsCount)
                ->description($pendingApprovalsCount > 0 ? 'New payroll reviews required' : 'No pending approvals')
                ->descriptionIcon($pendingApprovalsCount > 0 ? 'heroicon-m-exclamation-circle' : 'heroicon-m-check-badge')
                ->color($pendingApprovalsCount > 0 ? 'warning' : 'success')
                ->visible(fn () => Auth::check() && optional(Auth::user()->role)->role_name === 'Owner'),
        ];

        if (! $latestPeriod) {
            $stats[] = Stat::make('Status', 'No finalized payroll found');
            return $stats;
        }

        $start = Carbon::parse($latestPeriod->start_date);
        $end = Carbon::parse($latestPeriod->end_date);
        $calendarDays = $start->diffInDays($end) + 1;

        $payrolls = Payroll::with(['contribution', 'employee'])
            ->where('payroll_period_id', $latestPeriod->id)
            ->when(
                ! in_array($roleName, $allowedAllBranches),
                fn ($query) => $query->whereHas('employee', function ($employeeQuery) use ($user) {
                    $employeeQuery->where('branch_id', $user->branch_id);
                })
            )
            ->get();

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
            'total_sss' => 0,
            'total_premium_ss' => 0,
            'total_sss_loan' => 0,
            'total_sss_calamity_loan' => 0,
            'total_philhealth' => 0,
            'total_pagibig' => 0,
            'total_pagibig_loan' => 0,
        ];

        foreach ($payrolls as $payroll) {
            $totals['total_basic'] += $payroll->basic_salary ?? 0;
            $totals['total_ot'] += $payroll->overtime_salary ?? 0;
            $totals['total_nd'] += $payroll->night_diff_salary ?? 0;
            $totals['total_nd_ot'] += $payroll->night_diff_ot_salary ?? 0;
            $totals['total_sunday_ot'] += $payroll->sunday_ot_salary ?? 0;
            $totals['total_gross'] += $payroll->gross_pay ?? 0;
            $totals['total_ca'] += $payroll->cash_advance ?? 0;
            $totals['total_shortages'] += $payroll->shortages ?? 0;
            $totals['total_other'] += $payroll->other_deduction ?? 0;
            $totals['total_net'] += $payroll->net_pay ?? 0;

            if ($payroll->contribution) {
                $totals['total_sss'] += ($payroll->contribution->sss_ee ?? 0) + ($payroll->contribution->sss_er ?? 0);
                $totals['total_premium_ss'] += $payroll->contribution->premium_voluntary_ss_contribution ?? 0;
                $totals['total_sss_loan'] += $payroll->contribution->sss_salary_loan ?? 0;
                $totals['total_sss_calamity_loan'] += $payroll->contribution->sss_calamity_loan ?? 0;
                $totals['total_philhealth'] += ($payroll->contribution->philhealth_ee ?? 0) + ($payroll->contribution->philhealth_er ?? 0);
                $totals['total_pagibig'] += ($payroll->contribution->pagibig_ee ?? 0) + ($payroll->contribution->pagibig_er ?? 0);
                $totals['total_pagibig_loan'] += $payroll->contribution->pagibig_salary_loan ?? 0;
            }
        }

        $periodLabel = $start->format('M d') . ' - ' . $end->format('M d, Y');

        $scopeLabel = in_array($roleName, $allowedAllBranches)
            ? 'All branches'
            : 'Your branch only';

        $totalOvertimeCombined =
            $totals['total_ot']
            + $totals['total_nd']
            + $totals['total_nd_ot']
            + $totals['total_sunday_ot'];

        $stats[] = Stat::make('Period Duration', "{$calendarDays} Days")
            ->description($periodLabel . ' • ' . $scopeLabel)
            ->descriptionIcon('heroicon-m-calendar-days')
            ->color('primary');

        $stats[] = Stat::make('Total Basic Salary', '₱' . number_format($totals['total_basic'], 2))
            ->description('Total base pay • ' . $scopeLabel)
            ->descriptionIcon('heroicon-m-briefcase')
            ->color('info');

        $stats[] = Stat::make('Total Overtime Pay', '₱' . number_format($totalOvertimeCombined, 2))
            ->description('Includes OT, ND, ND OT, Sunday OT')
            ->descriptionIcon('heroicon-m-clock')
            ->color('info');

        $stats[] = Stat::make('Total Gross Pay', '₱' . number_format($totals['total_gross'], 2))
            ->description('Earnings before deductions • ' . $scopeLabel)
            ->descriptionIcon('heroicon-m-plus-circle')
            ->color('info');

        $stats[] = Stat::make('Total Cash Advance', '₱' . number_format($totals['total_ca'], 2))
            ->description('Outstanding cash advance • ' . $scopeLabel)
            ->descriptionIcon('heroicon-m-arrow-path')
            ->color('warning');

        $stats[] = Stat::make('Total Shortages', '₱' . number_format($totals['total_shortages'], 2))
            ->description('Accountability deductions • ' . $scopeLabel)
            ->descriptionIcon('heroicon-m-exclamation-triangle')
            ->color('danger');

        $stats[] = Stat::make('Total SSS Contribution', '₱' . number_format($totals['total_sss'], 2))
            ->description('Combined EE + ER SSS • ' . $scopeLabel)
            ->descriptionIcon('heroicon-m-building-office')
            ->color('success');

        $stats[] = Stat::make('Total Premium SS', '₱' . number_format($totals['total_premium_ss'], 2))
            ->description('Voluntary premium contribution • ' . $scopeLabel)
            ->descriptionIcon('heroicon-m-star')
            ->color('info');

        $stats[] = Stat::make('Total PhilHealth Contribution', '₱' . number_format($totals['total_philhealth'], 2))
            ->description('Combined EE + ER PhilHealth • ' . $scopeLabel)
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('success');

        $stats[] = Stat::make('Total PagIBIG Contribution', '₱' . number_format($totals['total_pagibig'], 2))
            ->description('Combined EE + ER PagIBIG • ' . $scopeLabel)
            ->descriptionIcon('heroicon-m-home')
            ->color('success');

        $stats[] = Stat::make('Total Net Pay Disbursement', '₱' . number_format($totals['total_net'], 2))
            ->description('Final payout amount • ' . $scopeLabel)
            ->descriptionIcon('heroicon-m-check-badge')
            ->color('success')
            ->extraAttributes(['class' => 'hover:scale-105 transition']);

        return $stats;
    }
}