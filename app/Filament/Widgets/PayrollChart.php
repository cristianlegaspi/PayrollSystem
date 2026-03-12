<?php

namespace App\Filament\Widgets;

use App\Models\Payroll;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PayrollChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Payroll Expense (Net Pay)';
    
    // This makes the chart span the full width of the dashboard
    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '400px'; // Increased height for better visibility

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Payroll::select(
            DB::raw('SUM(net_pay) as total'),
            DB::raw("DATE_FORMAT(created_at, '%M') as month"),
            DB::raw("MONTH(created_at) as month_num")
        )
        ->whereYear('created_at', date('Y'))
        ->groupBy('month', 'month_num')
        ->orderBy('month_num')
        ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Net Pay Disbursement',
                    'data' => $data->pluck('total')->toArray(),
                    'fill' => 'start',
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $data->pluck('month')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}