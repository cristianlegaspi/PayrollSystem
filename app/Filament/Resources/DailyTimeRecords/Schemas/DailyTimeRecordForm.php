<?php

namespace App\Filament\Resources\DailyTimeRecords\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Section;
use App\Models\Branch;
use Carbon\Carbon;

class DailyTimeRecordForm
{
    public static function configure($schema)
    {
        return $schema
            ->components([

                Section::make('Employee & Date')
                    ->schema([

                        Select::make('branch_id')
                            ->label('Branch')
                            ->options(function () {

                                $user = Filament::auth()->user();
                                $roleName = $user->role?->role_name;

                                if (in_array($roleName, ['Admin', 'Super Admin'])) {
                                    return Branch::pluck('branch_name', 'id');
                                }

                                return [$user->branch_id => $user->branch?->branch_name];
                            })
                            ->default(fn() => Filament::auth()->user()->branch_id)
                            ->reactive()
                            ->afterStateUpdated(fn ($set) => $set('employee_id', null))
                            ->required(),

                        Select::make('employee_id')
                            ->label('Employee')
                            ->relationship(
                                name: 'employee',
                                titleAttribute: 'full_name',
                                modifyQueryUsing: function ($query, $get) {

                                    $user = Filament::auth()->user();
                                    $roleName = $user->role?->role_name;

                                    if (in_array($roleName, ['Admin', 'Super Admin'])) {
                                        return $get('branch_id')
                                            ? $query->where('branch_id', $get('branch_id'))
                                            : $query;
                                    }

                                    return $query->where('branch_id', $user->branch_id);
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('work_date')
                            ->default(now())
                            ->required(),

                    ])->columns(3),

                Section::make('Attendance Status')
                    ->schema([

                        Select::make('status')
                            ->label('Attendance Status')
                            ->options([
                                'on_duty' => 'On Duty',
                                'night_shift' => 'Night Shift',
                                'rest_day' => 'Rest Day',
                                'legal_holiday' => 'Legal Holiday',
                                'special_holiday' => 'Special Holiday',
                                'absent_with_pay' => 'Absent With Pay',
                                'absent_without_pay' => 'Absent Without Pay',
                            ])
                            ->default('on_duty')
                            ->reactive()
                            ->afterStateUpdated(fn($get, $set) => self::compute($get, $set))
                            ->required(),

                        TextInput::make('remarks')
                            ->label('System Remarks')
                            ->readOnly()
                            ->extraAttributes(['class' => 'font-bold text-primary-600']),

                    ])->columns(2),

                Section::make('Biometrics Details')
                    ->schema([

                        Section::make('1st Shift')
                            ->schema([
                                TimePicker::make('shift1_time_in')
                                    ->seconds(true)
                                    ->reactive()
                                    ->afterStateUpdated(fn($get, $set) => self::compute($get, $set)),

                                TimePicker::make('shift1_time_out')
                                    ->seconds(true)
                                    ->reactive()
                                    ->afterStateUpdated(fn($get, $set) => self::compute($get, $set)),
                            ])->columns(2),

                        Section::make('2nd Shift')
                            ->schema([
                                TimePicker::make('shift2_time_in')
                                    ->seconds(true)
                                    ->reactive()
                                    ->afterStateUpdated(fn($get, $set) => self::compute($get, $set)),

                                TimePicker::make('shift2_time_out')
                                    ->seconds(true)
                                    ->reactive()
                                    ->afterStateUpdated(fn($get, $set) => self::compute($get, $set)),
                            ])->columns(2),

                        Section::make('3rd Shift')
                            ->schema([
                                TimePicker::make('shift3_time_in')
                                    ->seconds(true)
                                    ->reactive()
                                    ->afterStateUpdated(fn($get, $set) => self::compute($get, $set)),

                                TimePicker::make('shift3_time_out')
                                    ->seconds(true)
                                    ->reactive()
                                    ->afterStateUpdated(fn($get, $set) => self::compute($get, $set)),
                            ])->columns(2),

                    ])->columns(1),

                Section::make('Totals')
                    ->schema([

                        TextInput::make('total_hours')
                            ->label('Regular Hours (Payable)')
                            ->numeric()
                            ->readOnly()
                            ->default(0),

                        TextInput::make('overtime_hours')
                            ->label('Overtime Hours')
                            ->numeric()
                            ->readOnly()
                            ->default(0),

                        TextInput::make('undertime_hours')
                            ->label('Undertime Hours')
                            ->numeric()
                            ->readOnly()
                            ->default(0),

                        TextInput::make('night_diff_hours')
                            ->label('Night Diff Hours')
                            ->numeric()
                            ->readOnly()
                            ->default(0),

                        TextInput::make('night_diff_ot_hours')
                            ->label('Night OT Hours')
                            ->numeric()
                            ->readOnly()
                            ->default(0),

                    ])->columns(2),

            ])->columns(1);
    }

   protected static function compute($get, $set)
{
    $status = $get('status');

    $totalMinutes = 0;
    $nightMinutes = 0;

    for ($i = 1; $i <= 3; $i++) {

        $timeIn = $get("shift{$i}_time_in");
        $timeOut = $get("shift{$i}_time_out");

        if (!$timeIn || !$timeOut) continue;

        $in = Carbon::parse($timeIn);
        $out = Carbon::parse($timeOut);

        if ($out <= $in) {
            $out->addDay();
        }

        $totalMinutes += $in->diffInMinutes($out);

        $cursor = $in->copy();

        while ($cursor < $out) {

            $hour = (int) $cursor->format('H');

            if ($hour >= 22 || $hour < 6) {
                $nightMinutes++;
            }

            $cursor->addMinute();
        }
    }

    $workedHours = round($totalMinutes / 60, 2);
    $nightHours = round($nightMinutes / 60, 2);

    $regular = 0;
    $ot = 0;
    $undertime = 0;
    $nightOT = 0;

    switch ($status) {

        case 'absent_without_pay':
            $regular = 0;
            $undertime = 0;
            $set('remarks', 'Absent Without Pay');
            break;

        case 'absent_with_pay':
            $regular = 8;
            $set('remarks', 'Absent With Pay');
            break;

        case 'legal_holiday':
            $regular = 8;
            $ot = $workedHours;
            $set('remarks', 'Legal Holiday');
            break;

        case 'rest_day':
            $ot = $workedHours;
            $set('remarks', 'Rest Day');
            break;

        case 'special_holiday':
            $ot = $workedHours;
            $set('remarks', 'Special Holiday');
            break;

        default:

            if ($workedHours >= 8) {
                $regular = 8;
                $ot = round($workedHours - 8, 2);
                $set('remarks', 'On Duty');
            } elseif ($workedHours > 0) {
                $regular = $workedHours;
                $undertime = round(8 - $workedHours, 2);
                $set('remarks', 'Undertime');
            } else {
                $set('remarks', 'Absent Without Pay');
            }

            break;
    }

    if ($ot > 0 && $nightHours > 0) {
        $nightOT = min($nightHours, $ot);
    }

    $set('total_hours', $regular);
    $set('overtime_hours', $ot);
    $set('undertime_hours', $undertime);
    $set('night_diff_hours', $nightHours);
    $set('night_diff_ot_hours', $nightOT);
}
}