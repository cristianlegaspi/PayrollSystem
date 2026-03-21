<?php

namespace App\Filament\Resources\DailyTimeRecords\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Section;
use App\Models\Employee;
use Carbon\Carbon;

class DailyTimeRecordForm
{
    public static function configure($schema)
    {
        return $schema->components([

            // =========================
            // Employee & Date Section
            // =========================
            Section::make('Employee & Date')
                ->schema([
                    Select::make('employee_id')
                        ->label('Employee')
                        ->options(function () {
                            $user = Filament::auth()->user();
                            $roleName = $user->role?->role_name;

                            if (in_array($roleName, ['Admin', 'Super Admin'])) {
                                return Employee::pluck('full_name', 'id');
                            }

                            return Employee::where('branch_id', $user->branch_id)
                                ->pluck('full_name', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->required(),

                    DatePicker::make('work_date')
                        ->default(now())
                        ->reactive()
                        ->afterStateUpdated(fn ($get, $set) => self::compute($get, $set))
                        ->required(),
                ])
                ->columns(2),

            // =========================
            // Attendance Status
            // =========================
            Section::make('Attendance Status')
                ->schema([
                    Select::make('status')
                        ->label('Attendance Status')
                        ->options([
                            'on_duty'             => 'On Duty',
                            'night_shift'         => 'Night Shift',
                            'rest_day'            => 'Rest Day',
                            'legal_holiday'       => 'Legal Holiday',
                            'special_holiday'     => 'Special Holiday',
                            'absent_with_pay'     => 'Absent With Pay',
                            'absent_without_pay'  => 'Absent Without Pay',
                        ])
                        ->default('on_duty')
                        ->reactive()
                        ->afterStateUpdated(fn ($get, $set) => self::compute($get, $set))
                        ->required(),
                ])
                ->columns(1),

            // =========================
            // Biometrics Details
            // =========================
            Section::make('Biometrics Details')
                ->schema([
                    Section::make('1st Shift')
                        ->schema([
                            TimePicker::make('shift1_time_in')->seconds(true)->reactive()->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),
                            TimePicker::make('shift1_time_out')->seconds(true)->reactive()->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),
                        ])->columns(2),

                    Section::make('2nd Shift')
                        ->schema([
                            TimePicker::make('shift2_time_in')->seconds(true)->reactive()->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),
                            TimePicker::make('shift2_time_out')->seconds(true)->reactive()->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),
                        ])->columns(2),

                    Section::make('3rd Shift')
                        ->schema([
                            TimePicker::make('shift3_time_in')->seconds(true)->reactive()->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),
                            TimePicker::make('shift3_time_out')->seconds(true)->reactive()->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),
                        ])->columns(2),
                ])->columns(1),

            // =========================
            // Totals
            // =========================
            Section::make('Totals')
                ->schema([
                    TextInput::make('total_hours')->label('Regular Hours (Payable)')->numeric()->readOnly()->default(0),
                    TextInput::make('overtime_hours')->label('Overtime Hours')->numeric()->readOnly()->default(0),
                    TextInput::make('sunday_ot_hours')->label('Sunday OT Hours')->numeric()->readOnly()->default(0),
                    TextInput::make('undertime_hours')->label('Undertime Hours')->numeric()->readOnly()->default(0),
                    TextInput::make('night_diff_hours')->label('Night Diff Hours')->numeric()->readOnly()->default(0),
                    TextInput::make('night_diff_ot_hours')->label('Night OT Hours')->numeric()->readOnly()->default(0),
                ])->columns(2),

            // =========================
            // System Remarks
            // =========================
            Section::make('System Remarks')
                ->schema([
                    TextInput::make('remarks')
                        ->label('System Remarks')
                        ->readOnly()
                        ->extraAttributes(['class' => 'font-bold text-primary-600'])
                ])->columns(1),
        ])->columns(1);
    }

    protected static function compute($get, $set)
    {
        $status     = $get('status');
        $workDate   = $get('work_date');
        $isSunday   = $workDate ? Carbon::parse($workDate)->isSunday() : false;

        $totalMinutes = 0;
        $nightMinutes = 0;

        for ($i = 1; $i <= 3; $i++) {
            $timeIn  = $get("shift{$i}_time_in");
            $timeOut = $get("shift{$i}_time_out");

            if (!$timeIn || !$timeOut) continue;

            $in  = Carbon::parse($timeIn);
            $out = Carbon::parse($timeOut);

            if ($out <= $in) $out->addDay();

            $totalMinutes += $in->diffInMinutes($out);

            $cursor = $in->copy();
            while ($cursor < $out) {
                $hour = (int) $cursor->format('H');
                
                // Logic: ND is 10PM (22) to 5AM (4:59). 
                // By using < 5, we exclude the 5:00 AM to 6:00 AM hour from ND.
                if ($hour >= 22 || $hour < 5) {
                    $nightMinutes++;
                }
                $cursor->addMinute();
            }
        }

        $workedHours = round($totalMinutes / 60, 2);
        $nightHours  = round($nightMinutes / 60, 2);

        $regular = 0;
        $ot = 0;
        $undertime = 0;
        $nightOT = 0;
        $sundayOT = 0;

        if ($isSunday && $workedHours > 0) {
            $sundayOT = $workedHours;
            $set('remarks', 'Sunday OT');
        } else {
            switch ($status) {
                case 'absent_without_pay':
                    $regular = 0;
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
                case 'special_holiday':
                    $ot = $workedHours;
                    $set('remarks', ucfirst(str_replace('_', ' ', $status)));
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
        }

        // Night OT logic
        if ($ot > 0 && $nightHours > 0) {
            $nightOT = min($nightHours, $ot);
        }

        $set('total_hours', $regular);
        $set('overtime_hours', $ot);
        $set('sunday_ot_hours', $sundayOT);
        $set('undertime_hours', $undertime);
        $set('night_diff_hours', $nightHours);
        $set('night_diff_ot_hours', $nightOT);
    }
}