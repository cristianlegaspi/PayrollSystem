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
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),

                    DatePicker::make('work_date')
                        ->default(now())
                        ->reactive()
                        ->afterStateUpdated(fn ($get, $set) => self::compute($get, $set))
                        ->required(),
                ])
                ->columns(2),

            Section::make('Attendance Status')
                ->schema([
                    Select::make('status')
                        ->label('Attendance Status')
                        ->options([
                            'on_duty'           => 'On Duty',
                            'night_shift'       => 'Night Shift',
                            'rest_day'          => 'Rest Day',
                            'legal_holiday'     => 'Legal Holiday',
                            'special_holiday'   => 'Special Holiday',
                            'absent_with_pay'   => 'Absent With Pay',
                            'absent_without_pay' => 'Absent Without Pay',
                        ])
                        ->default('on_duty')
                        ->reactive()
                        ->afterStateUpdated(fn ($get, $set) => self::compute($get, $set))
                        ->required(),
                ])
                ->columns(1),

            Section::make('Biometrics Details')
                ->schema([
                    Section::make('Shift Logs')
                        ->schema([
                            TimePicker::make('shift1_time_in')->label('1st In')->seconds(true)->reactive()->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),
                            TimePicker::make('shift1_time_out')->label('1st Out')->seconds(true)->reactive()->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),
                            
                            TimePicker::make('shift2_time_in')->label('2nd In')->seconds(true)->reactive()->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),
                            TimePicker::make('shift2_time_out')->label('2nd Out')->seconds(true)->reactive()->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),
                            
                            TimePicker::make('shift3_time_in')->label('3rd In')->seconds(true)->reactive()->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),
                            TimePicker::make('shift3_time_out')->label('3rd Out')->seconds(true)->reactive()->afterStateUpdated(fn ($get, $set) => self::compute($get, $set)),
                        ])->columns(2),
                ])->columns(1),

            Section::make('Totals')
                ->schema([
                    TextInput::make('total_hours')->label('Regular Hours')->numeric()->readOnly()->default(0),
                    TextInput::make('overtime_hours')->label('Total OT Hours')->numeric()->readOnly()->default(0),
                    TextInput::make('rest_day_ot_hours')->label('Rest Day OT')->numeric()->readOnly()->default(0),
                    TextInput::make('sunday_ot_hours')->label('Sunday OT')->numeric()->readOnly()->default(0),
                    TextInput::make('undertime_hours')->label('Undertime')->numeric()->readOnly()->default(0),
                    TextInput::make('night_diff_hours')->label('Night Diff (Total)')->numeric()->readOnly()->default(0),
                    TextInput::make('night_diff_ot_hours')->label('Night OT Hours')->numeric()->readOnly()->default(0),
                ])->columns(2),

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
        $employeeId = $get('employee_id');
        $isSunday   = $workDate ? Carbon::parse($workDate)->isSunday() : false;

        $employee = $employeeId ? Employee::with('employmentType')->find($employeeId) : null;
        $employmentTypeName = $employee?->employmentType?->name ?? '';
        $user = Filament::auth()->user();
        $userRole = $user->role?->role_name;

        $totalMinutes = 0;
        $nightDiffMinutes = 0;
        $nightOTMinutes = 0;
        $accumulatedMinutes = 0;

        for ($i = 1; $i <= 3; $i++) {
            $timeIn  = $get("shift{$i}_time_in");
            $timeOut = $get("shift{$i}_time_out");

            if (!$timeIn || !$timeOut) continue;

            $in  = Carbon::parse($timeIn);
            $out = Carbon::parse($timeOut);

            if ($out <= $in) $out->addDay();

            $cursor = $in->copy();

            while ($cursor < $out) {
                $totalMinutes++;
                $accumulatedMinutes++; 
                
                $hour = (int) $cursor->format('H');

                if ($hour >= 22 || $hour < 6) {
                    $nightDiffMinutes++;
                    if ($accumulatedMinutes > 480) {
                        $nightOTMinutes++;
                    }
                }
                $cursor->addMinute();
            }
        }

        $workedHours    = round($totalMinutes / 60, 2);
        $nightDiffTotal = round($nightDiffMinutes / 60, 2);
        $nightOTTotal   = round($nightOTMinutes / 60, 2);

        if ($nightDiffTotal <= 1.0 && $workedHours >= 8 && !str_contains($status, 'night')) {
            $nightDiffTotal = 0;
            $nightOTTotal = 0;
        }

        $regular = 0;
        $ot = 0;
        $undertime = 0;
        $sundayOT = 0;
        $restDayOT = 0;

        // --- LOGIC FOR SUNDAY VS FIELD ---
        
        if ($status === 'rest_day') {
            $restDayOT = $workedHours;
            $set('remarks', ($workedHours > 0) ? 'Rest Day OT' : 'Rest Day');
        } elseif ($isSunday && $employmentTypeName !== 'Field' && $workedHours > 0) {
            if (!in_array($userRole, ['Admin', 'Super Admin'])) {
                $ot = $workedHours;
                $set('remarks', 'Sunday (Regular OT)');
            } else {
                $sundayOT = $workedHours;
                $set('remarks', 'Sunday OT');
            }
        } else {
            switch ($status) {
                case 'absent_with_pay':
                    $regular = 8;
                    $set('remarks', 'Absent With Pay');
                    break;

                case 'legal_holiday':
                    if ($workedHours >= 8) {
                        $regular = 8;
                        $ot = round($workedHours - 8, 2);
                        $set('remarks', $ot > 0 ? 'Legal Holiday w/ OT' : 'Legal Holiday');
                    } elseif ($workedHours > 0) {
                        $regular = $workedHours;
                        $undertime = round(8 - $workedHours, 2);
                        $set('remarks', 'Legal Holiday (Undertime)');
                    } else {
                        $regular = 8;
                        $set('remarks', 'Legal Holiday (No Work)');
                    }
                    break;

                case 'special_holiday':
                    if ($workedHours > 0) {
                        $ot = $workedHours;
                        $set('remarks', 'Special Holiday');
                    } else {
                        $set('remarks', 'Special Holiday (No Work)');
                    }
                    break;

                default:
                    if ($workedHours >= 8) {
                        $regular = 8;
                        $ot = round($workedHours - 8, 2);
                        $label = ($workedHours > 8) ? 'On Duty w/ OT' : 'On Duty';
                        $set('remarks', $label);
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

        $set('total_hours', $regular);
        $set('overtime_hours', $ot);
        $set('rest_day_ot_hours', $restDayOT);
        $set('sunday_ot_hours', $sundayOT);
        $set('undertime_hours', $undertime);
        $set('night_diff_hours', $nightDiffTotal);
        $set('night_diff_ot_hours', $nightOTTotal);
    }
}