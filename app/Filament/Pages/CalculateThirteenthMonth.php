<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\ThirteenthMonthOverride;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class CalculateThirteenthMonth extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = '13th Month Pay';

    protected static string | \UnitEnum | null $navigationGroup = 'Payroll Management';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.calculate-thirteenth-month';

    public int | string $year;

    public int $dividend = 12;

    /**
     * Final values displayed in the table.
     * Priority:
     * 1. Saved manual override
     * 2. Payroll gross_pay
     */
    public array $thirteenthMonthValues = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user || ! $user->role) {
            return false;
        }

        return in_array($user->role->role_name, [
            'Admin',
            'Super Admin',
            'Owner',
        ]);
    }

    public function mount(): void
    {
        $this->year = (int) request()->get('year', Carbon::now()->year);

        $this->preloadThirteenthMonthValues();
    }

    /**
     * Do NOT use hydrate() here.
     * hydrate() can reload the data on every Livewire request
     * and can cause manually saved values to appear overwritten.
     */

    public function preloadThirteenthMonthValues(): void
    {
        $this->thirteenthMonthValues = [];

        $employees = Employee::query()
            ->where(function ($query) {
                $query
                    ->whereHas('payrolls.period', function ($payrollQuery) {
                        $payrollQuery->whereYear('start_date', (int) $this->year);
                    })
                    ->orWhereHas('thirteenthMonthOverrides', function ($overrideQuery) {
                        $overrideQuery->where('year', (int) $this->year);
                    });
            })
            ->with([
                'payrolls' => function ($query) {
                    $query->whereHas('period', function ($periodQuery) {
                        $periodQuery->whereYear('start_date', (int) $this->year);
                    });
                },
                'payrolls.period',
                'thirteenthMonthOverrides' => function ($query) {
                    $query->where('year', (int) $this->year);
                },
            ])
            ->get();

        foreach ($employees as $employee) {
            for ($monthNumber = 1; $monthNumber <= 12; $monthNumber++) {
                $payrollGrossPay = $employee->payrolls
                    ->filter(function ($payroll) use ($monthNumber) {
                        return $payroll->period
                            && Carbon::parse($payroll->period->start_date)->month === $monthNumber
                            && Carbon::parse($payroll->period->start_date)->year === (int) $this->year;
                    })
                    ->sum('gross_pay');

                $savedOverride = $employee->thirteenthMonthOverrides
                    ->where('month', $monthNumber)
                    ->first();

                $this->thirteenthMonthValues[$employee->id][$monthNumber] = $savedOverride
                    ? (float) $savedOverride->gross_pay_override
                    : (float) ($payrollGrossPay ?? 0);
            }
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->select('employees.*')
                    ->where(function ($query) {
                        $query
                            ->whereHas('payrolls.period', function ($payrollQuery) {
                                $payrollQuery->whereYear('start_date', (int) $this->year);
                            })
                            ->orWhereHas('thirteenthMonthOverrides', function ($overrideQuery) {
                                $overrideQuery->where('year', (int) $this->year);
                            });
                    })
                    ->with([
                        'position',
                        'payrolls.period',
                        'thirteenthMonthOverrides' => function ($query) {
                            $query->where('year', (int) $this->year);
                        },
                    ])
            )
            ->columns([
                TextColumn::make('full_name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes([
                        'class' => 'font-semibold sticky left-0 bg-white dark:bg-gray-900 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] w-64 min-w-[240px] truncate max-w-[280px]',
                    ]),

                $this->makeMonthlyColumn('jan', 1),
                $this->makeMonthlyColumn('feb', 2),
                $this->makeMonthlyColumn('mar', 3),
                $this->makeMonthlyColumn('apr', 4),
                $this->makeMonthlyColumn('may', 5),
                $this->makeMonthlyColumn('jun', 6),
                $this->makeMonthlyColumn('jul', 7),
                $this->makeMonthlyColumn('aug', 8),
                $this->makeMonthlyColumn('sep', 9),
                $this->makeMonthlyColumn('oct', 10),
                $this->makeMonthlyColumn('nov', 11),
                $this->makeMonthlyColumn('dec', 12),

                TextColumn::make('total_gross_earned')
                    ->label('Total Gross Pay')
                    ->money('PHP')
                    ->alignEnd()
                    ->weight('bold')
                    ->extraAttributes([
                        'class' => 'w-36 min-w-[140px] font-mono',
                    ])
                    ->state(fn (Employee $record) => $this->calculateEmployeeTotalGross($record->id)),

                TextColumn::make('thirteenth_month_pay')
                    ->label(fn () => "13th Month (÷{$this->dividend})")
                    ->money('PHP')
                    ->color('success')
                    ->weight('bold')
                    ->alignEnd()
                    ->extraAttributes([
                        'class' => 'w-36 min-w-[140px] font-mono',
                    ])
                    ->state(fn (Employee $record) => $this->calculateEmployeeTotalGross($record->id) / $this->dividend),
            ])
            ->defaultSort('full_name', 'asc');
    }

    protected function makeMonthlyColumn(string $monthName, int $monthNumber): TextColumn
    {
        return TextColumn::make("month_pay_{$monthName}")
            ->label(ucfirst($monthName))
            ->money('PHP')
            ->alignEnd()
            ->extraAttributes([
                'class' => 'w-24 min-w-[95px] max-w-[120px] sm:w-28 md:w-32 font-mono',
            ])
            ->state(function (Employee $record) use ($monthNumber) {
                return (float) ($this->thirteenthMonthValues[$record->id][$monthNumber] ?? 0);
            });
    }

    public function calculateEmployeeTotalGross(int $employeeId): float
    {
        if (! isset($this->thirteenthMonthValues[$employeeId])) {
            return 0.0;
        }

        return array_sum(array_map('floatval', $this->thirteenthMonthValues[$employeeId]));
    }

    public function getGrandTotals(?int $employeeId = null): array
    {
        $grandTotalGross = 0;

        foreach ($this->thirteenthMonthValues as $id => $months) {
            if ($employeeId !== null && (int) $id !== (int) $employeeId) {
                continue;
            }

            $grandTotalGross += array_sum(array_map('floatval', $months));
        }

        return [
            'gross' => $grandTotalGross,
            'thirteenth' => $grandTotalGross / $this->dividend,
        ];
    }

    public function getPrintData(?int $employeeId = null): array
    {
        $query = Employee::query()
            ->where(function ($query) {
                $query
                    ->whereHas('payrolls.period', function ($payrollQuery) {
                        $payrollQuery->whereYear('start_date', (int) $this->year);
                    })
                    ->orWhereHas('thirteenthMonthOverrides', function ($overrideQuery) {
                        $overrideQuery->where('year', (int) $this->year);
                    });
            });

        if ($employeeId !== null) {
            $query->where('id', $employeeId);
        }

        $employees = $query->get()->sortBy('full_name');

        $data = [];

        foreach ($employees as $employee) {
            $months = $this->thirteenthMonthValues[$employee->id] ?? array_fill(1, 12, 0.0);

            $months = array_map('floatval', $months);

            $totalGross = array_sum($months);

            $thirteenthMonthPay = $totalGross / $this->dividend;

            $data[] = [
                'name' => $employee->full_name,
                'months' => $months,
                'total_gross' => $totalGross,
                'thirteenth_pay' => $thirteenthMonthPay,
            ];
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printSummary')
                ->label('Print Summary')
                ->icon('heroicon-m-printer')
                ->color('info')
                ->action(function () {
                    session()->put('thirteenth_month_print_data', [
                        'year' => $this->year,
                        'dividend' => $this->dividend,
                        'is_single' => false,
                        'employees' => $this->getPrintData(),
                        'grand_totals' => $this->getGrandTotals(),
                    ]);

                    $this->dispatch('open-print-preview');
                }),

            Action::make('filterOptions')
                ->label(fn () => "Settings (Year: {$this->year} | Dividend: ÷{$this->dividend})")
                ->icon('heroicon-m-adjustments-horizontal')
                ->form([
                    Select::make('year')
                        ->label('Select Calendar Year')
                        ->options(
                            array_combine(
                                range(Carbon::now()->year, 2024),
                                range(Carbon::now()->year, 2024)
                            )
                        )
                        ->default(fn () => $this->year)
                        ->required(),

                    Select::make('dividend')
                        ->label('Calculate Pay Basis (Dividend)')
                        ->options([
                            12 => 'Divide by 12 (Standard Full Year Basis)',
                            6 => 'Divide by 6 (Mid-Year / Custom Basis)',
                        ])
                        ->default(fn () => $this->dividend)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->year = (int) $data['year'];

                    $this->dividend = (int) $data['dividend'];

                    $this->preloadThirteenthMonthValues();

                    $this->resetTable();
                })
                ->button(),
        ];
    }
}