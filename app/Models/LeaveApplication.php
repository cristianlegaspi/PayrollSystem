<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LeaveBalance;

class LeaveApplication extends Model
{
    protected $fillable = [

        'employee_id',
        'from_date',
        'to_date',
        'days',
        'leave_type',
        'reason',
        'status',
        'approved_date',


    ];

    protected $casts = [

        'from_date' => 'date',
        'to_date' => 'date',
        'approved_date' => 'datetime',

    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (LeaveApplication $leave) {

            // Only restore credits if the leave was approved.
            if ($leave->status !== 'Approved') {
                return;
            }

            $balance = LeaveBalance::where('employee_id', $leave->employee_id)
                ->where('year', $leave->from_date->year)
                ->first();

            if (! $balance) {
                return;
            }

            $balance->used_credit -= $leave->days;
            $balance->remaining_credit += $leave->days;

            // Prevent negative values.
            if ($balance->used_credit < 0) {
                $balance->used_credit = 0;
            }

            // Do not exceed the annual credit.
            if ($balance->remaining_credit > $balance->annual_credit) {
                $balance->remaining_credit = $balance->annual_credit;
            }

            $balance->save();
        });
    }
}
