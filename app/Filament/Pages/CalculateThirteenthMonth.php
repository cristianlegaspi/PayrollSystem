<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Employee;
use App\Models\ThirteenthMonthOverride;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Carbon\Carbon;

class CalculateThirteenthMonth extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = '13th Month Pay';
    protected static string|\UnitEnum|null $navigationGroup = 'Payroll Management';
    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.calculate-thirteenth-month';

    public $year;
    public int $dividend = 12;
    public array $overrides = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user || ! $user->role) {
            return false;
        }

        return in_array($user->role->role_name, ['Admin', 'Super Admin', 'Owner']);
    }

    public function mount()
    {
        $this->year = request()->get('year', Carbon::now()->year);
        $this->preloadDatabaseValues();
    }

    /**
     * Load values into the editable matrix (Combines Overrides Table & Payrolls Log fallback)
     */
    public function preloadDatabaseValues()
    {
        $employees = Employee::whereHas('payrolls.period', function ($query) {
                $query->whereYear('start_date', $this->year);
            })
            ->orWhereHas('thirteenthMonthOverrides', function ($query) {
                $query->where('year', $this->year);
            })
            ->with(['payrolls.period', 'thirteenthMonthOverrides' => function($q) {
                $q->where('year', $this->year);
            }])
            ->get();

        foreach ($employees as $employee) {
            for ($m = 1; $m <= 12; $m++) {
                $savedOverride = $employee->thirteenthMonthOverrides
                    ->where('month', $m)
                    ->first();

                if ($savedOverride) {
                    $this->overrides[$employee->id][$m] = (float) $savedOverride->gross_pay_override;
                } else {
                    $dbAmount = $employee->payrolls
                        ->filter(fn ($p) =>
                            $p->period &&
                            Carbon::parse($p->period->start_date)->month === $m &&
                            Carbon::parse($p->period->start_date)->year == $this->year
                        )
                        ->sum('gross_pay');

                    $this->overrides[$employee->id][$m] = (float) ($dbAmount ?? 0);
                }
            }
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->whereHas('payrolls.period', function ($query) {
                        $query->whereYear('start_date', $this->year);
                    })
                    ->orWhereHas('thirteenthMonthOverrides', function ($query) {
                        $query->where('year', $this->year);
                    })
                    ->with(['position'])
            )
            ->columns([
                TextColumn::make('full_name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes([
                        'class' => 'font-semibold sticky left-0 bg-white dark:bg-gray-900 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]'
                    ]),

                // Monthly columns
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

                // Total Gross Pay
                TextColumn::make('total_gross_earned')
                    ->label('Total Gross Pay')
                    ->money('PHP')
                    ->alignEnd()
                    ->weight('bold')
                    ->state(fn (Employee $record) =>
                        $this->calculateEmployeeTotalGross($record->id)
                    ),

                // 13th Month Calculator
                TextColumn::make('thirteenth_month_pay')
                    ->label(fn () => "13th Month (÷{$this->dividend})")
                    ->money('PHP')
                    ->color('success')
                    ->weight('bold')
                    ->alignEnd()
                    ->state(fn (Employee $record) =>
                        $this->calculateEmployeeTotalGross($record->id) / $this->dividend
                    ),
            ])
            ->defaultSort('full_name', 'asc');
    }

    /**
     * FIXED: Utilizing ->key() instead of ->name() to safely isolate inputs by record ID string keys
     */
    protected function makeMonthlyColumn(string $monthName, int $monthNumber): TextInputColumn
    {
        return TextInputColumn::make("month_override_{$monthNumber}")
            ->label(ucfirst($monthName))
            ->alignEnd()
            ->type('number')
            // FIX: This forces Filament to give every single row text input its own specific layout identity marker string dynamically
            ->key(fn (Employee $record) => "emp_{$record->id}_m_{$monthNumber}")
            ->state(fn (Employee $record) =>
                $this->overrides[$record->id][$monthNumber] ?? 0
            )
            ->updateStateUsing(function (Employee $record, $state) use ($monthNumber) {
                $cleanedValue = (float) filter_var(
                    $state,
                    FILTER_SANITIZE_NUMBER_FLOAT,
                    FILTER_FLAG_ALLOW_FRACTION
                );

                // 1. Write updates exclusively for this targeted employee model record
                ThirteenthMonthOverride::updateOrCreate(
                    [
                        'employee_id' => $record->id,
                        'year'        => $this->year,
                        'month'       => $monthNumber,
                    ],
                    [
                        'gross_pay_override' => $cleanedValue,
                    ]
                );

                // 2. Keep the live memory object structure in complete sync
                $this->overrides[$record->id][$monthNumber] = $cleanedValue;

                return $cleanedValue;
            });
    }

    public function calculateEmployeeTotalGross(int $employeeId): float
    {
        if (! isset($this->overrides[$employeeId])) {
            return 0;
        }

        return array_sum($this->overrides[$employeeId]);
    }

    public function getGrandTotals(): array
    {
        $grandTotalGross = 0;
        foreach ($this->overrides as $employeeId => $months) {
            $grandTotalGross += array_sum($months);
        }

        return [
            'gross' => $grandTotalGross,
            'thirteenth' => $grandTotalGross / $this->dividend,
        ];
    }

    public function getPrintData(): array
    {
        $employees = Employee::whereHas('payrolls.period', function ($query) {
                $query->whereYear('start_date', $this->year);
            })
            ->orWhereHas('thirteenthMonthOverrides', function ($query) {
                $query->where('year', $this->year);
            })
            ->get()
            ->sortBy('full_name');

        $data = [];
        foreach ($employees as $employee) {
            $months = $this->overrides[$employee->id] ?? array_fill(1, 12, 0.0);
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
                        'employees' => $this->getPrintData(),
                        'grand_totals' => $this->getGrandTotals(),
                    ]);

                    $this->dispatch('open-print-preview');
                }),

            Action::make('filterOptions')
                ->label("Settings (Year: {$this->year} | Dividend: ÷{$this->dividend})")
                ->icon('heroicon-m-adjustments-horizontal')
                ->form([
                    Select::make('year')
                        ->label('Select Calendar Year')
                        ->options(array_combine(
                            range(Carbon::now()->year, 2024),
                            range(Carbon::now()->year, 2024)
                        ))
                        ->default($this->year)
                        ->required(),

                    Select::make('dividend')
                        ->label('Calculate Pay Basis (Dividend)')
                        ->options([
                            12 => 'Divide by 12 (Standard Full Year Basis)',
                            6  => 'Divide by 6 (Mid-Year / Custom Basis)',
                        ])
                        ->default($this->dividend)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->year = $data['year'];
                    $this->dividend = (int) $data['dividend'];
                    
                    $this->overrides = [];
                    $this->preloadDatabaseValues();
                    $this->resetTable();
                })
                ->button(),
        ];
    }
}