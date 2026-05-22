<?php

namespace App\Filament\Widgets;

use App\Models\Payroll;
use Filament\Widgets\ChartWidget;

class PayrollSummaryChart extends ChartWidget
{
    protected ?string $heading = 'Payroll Summary Chart';

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '314px';

    protected function getData(): array
    {
        /*
        |--------------------------------------------------------------------------
        | TOTAL GROSS PAY
        |--------------------------------------------------------------------------
        */

        $totalGrossPay = Payroll::query()
            ->sum('gross_pay');

        /*
        |--------------------------------------------------------------------------
        | TOTAL DEDUCTIONS
        |--------------------------------------------------------------------------
        */

        $totalDeductions = Payroll::query()
            ->sum('total_deductions');

        /*
        |--------------------------------------------------------------------------
        | TOTAL NET PAY
        |--------------------------------------------------------------------------
        */

        $totalNetPay = Payroll::query()
            ->sum('net_pay');

        /*
        |--------------------------------------------------------------------------
        | TOTAL OVERTIME PAY
        |--------------------------------------------------------------------------
        */

        $totalOvertimePay = Payroll::query()
            ->sum('overtime_salary');

        return [

            'datasets' => [
                [
                    'data' => [

                        $totalGrossPay,

                        $totalDeductions,

                        $totalNetPay,

                        $totalOvertimePay,

                    ],

                    'backgroundColor' => [

                        '#3b82f6',

                        '#ef4444',

                        '#10b981',

                        '#f59e0b',

                    ],

                    'borderWidth' => 1,

                ],
            ],

            'labels' => [

                'Gross Pay',

                'Deductions',

                'Net Pay',

                'Overtime Pay',

            ],

        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [

            'maintainAspectRatio' => false,

            'cutout' => '65%',

            'plugins' => [

                'legend' => [

                    'position' => 'bottom',

                    'labels' => [

                        'padding' => 15,

                        'boxWidth' => 12,

                    ],

                ],

            ],

        ];
    }
}