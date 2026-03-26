<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
     protected $fillable = [
        'employee_id',
        'payroll_period_id',
        'days_worked',
        'days_absent',
        'undertime_hours',
        'overtime_hours',
        'night_diff_hours',
        'night_diff_ot_hours',
        'daily_rate',
        'basic_salary',
        'overtime_salary',
        'night_diff_salary',
        'night_diff_ot_salary',
        'gross_pay',
        'total_deductions',
        'cash_advance',
        'shortages',    
        'other_deduction',
        'net_pay',
        'sunday_ot_hours',
        'sunday_ot_salary',
        'undertime_deduction',
        'rest_day_ot_hours',
        'rest_day_ot_salary',
        
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

        public function getIsAbsentAttribute()
    {
        return $this->status === 'absent_without_pay';
    }

        public function getIsPresentAttribute()
        {
            return in_array($this->status, ['on_duty', 'rest_day', 'legal_holiday']);
        }

        public function contribution()
{
    // Assuming contribution table has employee_id as FK
    return $this->hasOne(Contribution::class, 'employee_id', 'employee_id');
}

    protected static function booted()
    {
        static::saving(function ($payroll) {
            // Recompute net pay whenever record is saved
            $cashAdvance = $payroll->cash_advance ?? 0;
            $shortages = $payroll->shortages ?? 0;
            $other_deduction = $payroll->other_deduction ?? 0;

            $payroll->net_pay = $payroll->gross_pay - ($payroll->total_deductions + $cashAdvance + $shortages + $other_deduction);
        });
    }
}
