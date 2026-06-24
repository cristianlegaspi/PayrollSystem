<?php

namespace App\Filament\Widgets;

use App\Models\PayrollAdjustment;
use App\Models\PayrollPeriod;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class PayrollAdjustmentStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $latestPeriod = PayrollPeriod::query()
            ->orderByDesc('start_date')
            ->first();

        $baseQuery = PayrollAdjustment::query()
            ->with('employee');

        if ($latestPeriod) {
            $baseQuery->where('payroll_period_id', $latestPeriod->id);
        }

        $user = Filament::auth()->user();
        $roleName = strtolower(trim($user?->role?->role_name ?? ''));

        /*
         * Staff can only see totals from their own branch.
         * Admin, Super Admin, and Owner can see all totals.
         */
        if ($roleName === 'staff') {
            if (! $user?->branch_id) {
                $baseQuery->whereRaw('1 = 0');
            } else {
                $baseQuery->whereHas('employee', function (Builder $query) use ($user) {
                    $query->where('branch_id', $user->branch_id);
                });
            }
        }

        $totalCashAdvance = (clone $baseQuery)->sum('cash_advance');
        $totalShortages = (clone $baseQuery)->sum('shortages');
        $totalOtherDeduction = (clone $baseQuery)->sum('other_deduction');
        $totalOtherIncentives = (clone $baseQuery)->sum('other_incentives');

        return [
            Stat::make('Cash Advance', '₱ ' . number_format($totalCashAdvance, 2))
                ->description('Total cash advance')
                ->color('warning'),

            Stat::make('Shortages', '₱ ' . number_format($totalShortages, 2))
                ->description('Total shortages')
                ->color('danger'),

            Stat::make('Other Deductions', '₱ ' . number_format($totalOtherDeduction, 2))
                ->description('Additional deductions')
                ->color('danger'),

            Stat::make('Other Incentives', '₱ ' . number_format($totalOtherIncentives, 2))
                ->description('Additional incentives')
                ->color('success'),
        ];
    }
}