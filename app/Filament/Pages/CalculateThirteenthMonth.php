<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\ThirteenthMonthLock;
use App\Models\ThirteenthMonthOverride;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
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
     * This controls if all monthly fields are editable or read-only.
     * This is now saved in the database using thirteenth_month_locks table.
     */
    public bool $fieldsLocked = false;

    /**
     * This is the editable/live value shown in the table.
     */
    public array $overrides = [];

    /**
     * This stores the actual payroll gross pay from Payroll records.
     * Used to know if the user made a real manual override.
     */
    public array $basePayroll = [];

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

        $this->loadFieldsLockStatus();

        $this->preloadDatabaseValues();
    }

    public function loadFieldsLockStatus(): void
    {
        $this->fieldsLocked = (bool) ThirteenthMonthLock::query()
            ->where('year', (int) $this->year)
            ->value('is_locked');
    }

    public function saveFieldsLockStatus(): void
    {
        ThirteenthMonthLock::updateOrCreate(
            [
                'year' => (int) $this->year,
            ],
            [
                'is_locked' => $this->fieldsLocked,
            ]
        );
    }

    public function toggleFieldsLock(): void
    {
        $this->fieldsLocked = ! $this->fieldsLocked;

        $this->saveFieldsLockStatus();

        $this->resetTable();

        Notification::make()
            ->title($this->fieldsLocked ? 'Fields Locked' : 'Fields Unlocked')
            ->body($this->fieldsLocked
                ? 'All monthly fields are now locked and shown as read-only.'
                : 'All monthly fields are now editable again.'
            )
            ->success()
            ->send();
    }

    /**
     * Load 13th month values.
     *
     * Default source:
     * - Payroll gross_pay
     *
     * Optional source:
     * - ThirteenthMonthOverride, only if manually saved.
     */
    public function preloadDatabaseValues(): void
    {
        $this->overrides = [];
        $this->basePayroll = [];

        $employees = Employee::where(function ($masterQuery) {
            $masterQuery
                ->whereHas('payrolls.period', function ($query) {
                    $query->whereYear('start_date', $this->year);
                })
                ->orWhereHas('thirteenthMonthOverrides', function ($query) {
                    $query->where('year', $this->year);
                });
        })
            ->with([
                'payrolls.period',
                'thirteenthMonthOverrides' => function ($query) {
                    $query->where('year', $this->year);
                },
            ])
            ->get();

        foreach ($employees as $employee) {
            for ($monthNumber = 1; $monthNumber <= 12; $monthNumber++) {
                $payrollGrossPay = $employee->payrolls
                    ->filter(function ($payroll) use ($monthNumber) {
                        return $payroll->period
                            && Carbon::parse($payroll->period->start_date)->month === $monthNumber
                            && Carbon::parse($payroll->period->start_date)->year == $this->year;
                    })
                    ->sum('gross_pay');

                $payrollGrossPay = (float) ($payrollGrossPay ?? 0);

                $this->basePayroll[$employee->id][$monthNumber] = $payrollGrossPay;

                $savedOverride = $employee->thirteenthMonthOverrides
                    ->where('month', $monthNumber)
                    ->first();

                /**
                 * If override exists, use it.
                 * If no override, use current payroll gross pay.
                 */
                $this->overrides[$employee->id][$monthNumber] = $savedOverride
                    ? (float) $savedOverride->gross_pay_override
                    : $payrollGrossPay;
            }
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->select('employees.*')
                    ->where(function ($masterQuery) {
                        $masterQuery
                            ->whereHas('payrolls.period', function ($query) {
                                $query->whereYear('start_date', $this->year);
                            })
                            ->orWhereHas('thirteenthMonthOverrides', function ($query) {
                                $query->where('year', $this->year);
                            });
                    })
                    ->with([
                        'position',
                        'payrolls.period',
                        'thirteenthMonthOverrides' => function ($query) {
                            $query->where('year', $this->year);
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
            ->defaultSort('full_name', 'asc')
            ->actions([
                Action::make('printIndividual')
                    ->label('Print')
                    ->icon('heroicon-m-printer')
                    ->color('info')
                    ->action(function (Employee $record) {
                        session()->put('thirteenth_month_print_data', [
                            'year' => $this->year,
                            'dividend' => $this->dividend,
                            'is_single' => true,
                            'employees' => $this->getPrintData($record->id),
                            'grand_totals' => $this->getGrandTotals($record->id),
                        ]);

                        $this->dispatch('open-print-preview');
                    }),
            ]);
    }

    protected function makeMonthlyColumn(string $monthName, int $monthNumber): TextColumn
    {
        $column = TextColumn::make("month_override_{$monthName}")
            ->label(ucfirst($monthName))
            ->alignEnd()
            ->extraAttributes([
                'class' => 'w-24 min-w-[95px] max-w-[120px] sm:w-28 md:w-32 font-mono',
            ]);

        /**
         * If locked, do not use the editable Blade input.
         * Instead, show the value as plain read-only money text.
         */
        if ($this->fieldsLocked) {
            return $column
                ->money('PHP')
                ->state(function (Employee $record) use ($monthNumber) {
                    return (float) ($this->overrides[$record->id][$monthNumber] ?? 0);
                });
        }

        /**
         * If unlocked, use your existing editable Blade view.
         * No Blade changes needed.
         */
        return $column->view('filament.tables.columns.inline-matrix-input', [
            'monthNumber' => $monthNumber,
        ]);
    }

    public function calculateEmployeeTotalGross(int $employeeId): float
    {
        if (! isset($this->overrides[$employeeId])) {
            return 0.0;
        }

        return array_sum(array_map('floatval', $this->overrides[$employeeId]));
    }

    public function getGrandTotals(?int $employeeId = null): array
    {
        $grandTotalGross = 0;

        foreach ($this->overrides as $id => $months) {
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
        $query = Employee::query();

        if ($employeeId !== null) {
            $query->where('id', $employeeId);
        } else {
            $query->where(function ($masterQuery) {
                $masterQuery
                    ->whereHas('payrolls.period', function ($query) {
                        $query->whereYear('start_date', $this->year);
                    })
                    ->orWhereHas('thirteenthMonthOverrides', function ($query) {
                        $query->where('year', $this->year);
                    });
            });
        }

        $employees = $query->get()->sortBy('full_name');

        $data = [];

        foreach ($employees as $employee) {
            $months = $this->overrides[$employee->id] ?? array_fill(1, 12, 0.0);

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
            Action::make('refreshPayroll')
                ->label('Refresh from Payroll')
                ->icon('heroicon-m-arrow-path')
                ->color('gray')
                ->disabled(fn () => $this->fieldsLocked)
                ->action(function () {
                    $this->preloadDatabaseValues();

                    $this->resetTable();

                    Notification::make()
                        ->title('Refreshed')
                        ->body('13th month values were refreshed from payroll records.')
                        ->success()
                        ->send();
                }),

            Action::make('clearOverrides')
                ->label('Clear Saved Overrides')
                ->icon('heroicon-m-trash')
                ->color('danger')
                ->disabled(fn () => $this->fieldsLocked)
                ->requiresConfirmation()
                ->modalHeading('Clear saved 13th month overrides?')
                ->modalDescription('This will delete saved manual override values for the selected year. After clearing, the page will use the current payroll gross pay again.')
                ->action(function () {
                    ThirteenthMonthOverride::where('year', $this->year)->delete();

                    $this->preloadDatabaseValues();

                    $this->resetTable();

                    Notification::make()
                        ->title('Overrides Cleared')
                        ->body('Saved overrides were deleted. Current payroll gross pay is now being used.')
                        ->success()
                        ->send();
                }),

            Action::make('saveToDatabase')
                ->label('Save Manual Overrides')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->disabled(fn () => $this->fieldsLocked)
                ->requiresConfirmation()
                ->action(function () {
                    foreach ($this->overrides as $employeeId => $months) {
                        foreach ($months as $monthNumber => $value) {
                            $value = round((float) $value, 2);

                            $baseAmount = round(
                                (float) ($this->basePayroll[$employeeId][$monthNumber] ?? 0),
                                2
                            );

                            /**
                             * If the displayed value is same as Payroll gross_pay,
                             * do not save it as override.
                             */
                            if ($value === $baseAmount) {
                                ThirteenthMonthOverride::where('employee_id', $employeeId)
                                    ->where('year', $this->year)
                                    ->where('month', $monthNumber)
                                    ->delete();

                                continue;
                            }

                            /**
                             * Only save real manual changes.
                             */
                            ThirteenthMonthOverride::updateOrCreate(
                                [
                                    'employee_id' => $employeeId,
                                    'year' => $this->year,
                                    'month' => $monthNumber,
                                ],
                                [
                                    'gross_pay_override' => $value,
                                ]
                            );
                        }
                    }

                    $this->preloadDatabaseValues();

                    /**
                     * Automatically lock all fields after saving.
                     * This lock status is saved in the database.
                     */
                    $this->fieldsLocked = true;

                    $this->saveFieldsLockStatus();

                    $this->resetTable();

                    Notification::make()
                        ->title('Success')
                        ->body('Manual 13th month overrides saved successfully. All fields are now locked.')
                        ->success()
                        ->send();
                }),

            Action::make('toggleFieldsLock')
                ->label(fn () => $this->fieldsLocked ? 'Unlock Fields' : 'Lock All Fields')
                ->icon(fn () => $this->fieldsLocked ? 'heroicon-m-lock-open' : 'heroicon-m-lock-closed')
                ->color(fn () => $this->fieldsLocked ? 'warning' : 'danger')
                ->requiresConfirmation()
                ->modalHeading(fn () => $this->fieldsLocked ? 'Unlock all fields?' : 'Lock all fields?')
                ->modalDescription(fn () => $this->fieldsLocked
                    ? 'All monthly fields for this year will become editable again.'
                    : 'All monthly fields for this year will become read-only until unlocked.'
                )
                ->action(fn () => $this->toggleFieldsLock()),

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
                ->label("Settings (Year: {$this->year} | Dividend: ÷{$this->dividend})")
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
                        ->default($this->year)
                        ->required(),

                    Select::make('dividend')
                        ->label('Calculate Pay Basis (Dividend)')
                        ->options([
                            12 => 'Divide by 12 (Standard Full Year Basis)',
                            6 => 'Divide by 6 (Mid-Year / Custom Basis)',
                        ])
                        ->default($this->dividend)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->year = (int) $data['year'];

                    $this->dividend = (int) $data['dividend'];

                    /**
                     * Load the saved lock status for the selected year.
                     */
                    $this->loadFieldsLockStatus();

                    $this->preloadDatabaseValues();

                    $this->resetTable();
                })
                ->button(),
        ];
    }
}