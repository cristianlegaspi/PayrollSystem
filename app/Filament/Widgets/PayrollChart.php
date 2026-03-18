<?php

namespace App\Filament\Widgets;

use App\Models\Payroll;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PayrollChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Payroll Expense (Net Pay)';
    protected int|string|array $columnSpan = 'full';
    protected ?string $maxHeight = '400px';
    protected static ?int $sort = 2;
    

    protected function getData(): array
    {
        // Fetch total net pay grouped by month for the current year
        $data = Payroll::select(
            DB::raw('SUM(net_pay) as total'),
            DB::raw("DATE_FORMAT(created_at, '%M') as month"),
            DB::raw("MONTH(created_at) as month_num")
        )
        ->whereYear('created_at', date('Y'))
        ->groupBy('month', 'month_num')
        ->orderBy('month_num')
        ->get();

        // Ensure all 12 months are represented even if no payroll exists for some months
        $allMonths = collect(range(1, 12))->map(function ($m) {
            return [
                'month_num' => $m,
                'month' => date('F', mktime(0, 0, 0, $m, 1)),
            ];
        });

        $data = $allMonths->map(function ($month) use ($data) {
            $found = $data->firstWhere('month_num', $month['month_num']);
            return $found ? $found : ['month' => $month['month'], 'total' => 0];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Total Net Pay Disbursement',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#065f46',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->pluck('month')->toArray(),
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => [
                    'x' => [
                        'ticks' => [
                            'maxRotation' => 0, // straight labels
                            'minRotation' => 0,
                        ],
                    ],
                    'y' => [
                        'beginAtZero' => true, // no negative values
                        'ticks' => [
                            'callback' => 'function(value) { return "₱" + value.toLocaleString(); }',
                        ],
                    ],
                ],
                'plugins' => [
                    'legend' => [
                        'display' => true,
                        'position' => 'top',
                    ],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => 'function(context) { return "₱" + context.raw.toLocaleString(); }',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}