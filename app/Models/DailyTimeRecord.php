<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class DailyTimeRecord extends Model
{
      protected $fillable = [
        'employee_id',
        'work_date',
        'shift1_time_in',
        'shift1_time_out',
        'shift2_time_in',
        'shift2_time_out',
        'shift3_time_in',
        'shift3_time_out',
        'overtime_hours',
        'undertime_hours',
        'total_hours',
        'night_diff_hours',
        'night_diff_ot_hours',
        'sunday_ot_hours',
        'rest_day_ot_hours',
        'remarks',
        'status',
    ];

      protected $casts = [
        'work_date' => 'date',
        'overtime_hours' => 'decimal:2',
        'undertime_hours' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'night_diff_hours' => 'decimal:2',
        'night_diff_ot_hours' => 'decimal:2',
        'rest_day_ot_hours' => 'decimal:2', 
    ];

    

     public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    
      public function calculateTotalHours()
    {
        $totalMinutes = 0;

        $shifts = [
            ['shift1_time_in', 'shift1_time_out'],
            ['shift2_time_in', 'shift2_time_out'],
            ['shift3_time_in', 'shift3_time_out'],
        ];

        foreach ($shifts as $shift) {
            if ($this->{$shift[0]} && $this->{$shift[1]}) {

                $in = Carbon::parse($this->{$shift[0]});
                $out = Carbon::parse($this->{$shift[1]});

                if ($out->lt($in)) {
                    $out->addDay(); // for night shift
                }

                $totalMinutes += $out->diffInMinutes($in);
            }
        }

        $this->total_hours = round($totalMinutes / 60, 2);
    }
}
