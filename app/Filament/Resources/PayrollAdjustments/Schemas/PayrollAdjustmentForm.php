<?php

namespace App\Filament\Resources\PayrollAdjustments\Schemas;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayrollAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Employee and Payroll Cut-off')
                    ->description('Select the employee and payroll period where the deduction will apply.')
                    ->schema([
                        Select::make('employee_id')
                            ->label('Employee')
                            ->options(function () {
                                $user = Filament::auth()->user();
                                $roleName = $user?->role?->role_name;

                                if (in_array($roleName, ['Admin', 'Super Admin', 'Owner'])) {
                                    return Employee::query()
                                        ->orderBy('full_name')
                                        ->pluck('full_name', 'id');
                                }

                                return Employee::query()
                                    ->where('branch_id', $user?->branch_id)
                                    ->orderBy('full_name')
                                    ->pluck('full_name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive(),

                        Select::make('payroll_period_id')
                            ->label('Payroll Period / Cut-off')
                            ->options(function () {
                                return PayrollPeriod::query()
                                    ->where('status', 'open')
                                    ->orderByDesc('id')
                                    ->get()
                                    ->mapWithKeys(function (PayrollPeriod $period) {
                                        return [
                                            $period->id => self::payrollPeriodLabel($period),
                                        ];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Deductions and Incentives')
                    ->schema([
                        TextInput::make('cash_advance')
                            ->label('Cash Advance')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->required(),

                        TextInput::make('shortages')
                            ->label('Shortages')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->required(),

                        TextInput::make('other_deduction')
                            ->label('Other Deduction')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->required(),

                        TextInput::make('other_incentives')
                            ->label('Other Incentives')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Remarks')
                    ->schema([
                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    protected static function payrollPeriodLabel(PayrollPeriod $period): string
    {
        $start = $period->start_date
            ?? $period->date_from
            ?? $period->period_start
            ?? $period->cutoff_start
            ?? null;

        $end = $period->end_date
            ?? $period->date_to
            ?? $period->period_end
            ?? $period->cutoff_end
            ?? null;

        if ($start && $end) {
            return Carbon::parse($start)->format('M d, Y')
                . ' - '
                . Carbon::parse($end)->format('M d, Y');
        }

        $label = $period->period_name
            ?? $period->payroll_period
            ?? $period->cut_off
            ?? $period->cutoff
            ?? $period->title
            ?? $period->description
            ?? null;

        if ($label) {
            return $label;
        }

        return 'Payroll Period #' . $period->id;
    }
}