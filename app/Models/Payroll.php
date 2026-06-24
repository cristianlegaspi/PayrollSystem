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
        'other_incentives',
        'net_pay',
        'sunday_ot_hours',
        'sunday_ot_salary',
        'undertime_deduction',
        'rest_day_ot_hours',
        'rest_day_ot_salary',
    ];

    protected $casts = [
        'days_worked' => 'decimal:2',
        'days_absent' => 'decimal:2',
        'undertime_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'night_diff_hours' => 'decimal:2',
        'night_diff_ot_hours' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'overtime_salary' => 'decimal:2',
        'night_diff_salary' => 'decimal:2',
        'night_diff_ot_salary' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'cash_advance' => 'decimal:2',
        'shortages' => 'decimal:2',
        'other_deduction' => 'decimal:2',
        'other_incentives' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'sunday_ot_hours' => 'decimal:2',
        'sunday_ot_salary' => 'decimal:2',
        'undertime_deduction' => 'decimal:2',
        'rest_day_ot_hours' => 'decimal:2',
        'rest_day_ot_salary' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function contribution()
    {
        return $this->hasOne(Contribution::class, 'employee_id', 'employee_id');
    }

    public function payrollAdjustment()
    {
        return $this->hasOne(PayrollAdjustment::class, 'employee_id', 'employee_id')
            ->whereColumn('payroll_adjustments.payroll_period_id', 'payrolls.payroll_period_id');
    }

    public function payrollAdjustments()
    {
        return $this->hasMany(PayrollAdjustment::class, 'employee_id', 'employee_id')
            ->whereColumn('payroll_adjustments.payroll_period_id', 'payrolls.payroll_period_id');
    }

    public function getIsAbsentAttribute()
    {
        return $this->status === 'absent_without_pay';
    }

    public function getIsPresentAttribute()
    {
        return in_array($this->status, ['on_duty', 'rest_day', 'legal_holiday']);
    }

    protected static function booted(): void
    {
        static::saving(function (Payroll $payroll) {
            $payroll->syncAdjustmentsFromRecords();
            $payroll->recalculateTotals();
        });
    }

    public function syncAdjustmentsFromRecords(): void
    {
        if (! $this->employee_id || ! $this->payroll_period_id) {
            return;
        }

        $totals = PayrollAdjustment::query()
            ->where('employee_id', $this->employee_id)
            ->where('payroll_period_id', $this->payroll_period_id)
            ->selectRaw('
                COALESCE(SUM(cash_advance), 0) as cash_advance,
                COALESCE(SUM(shortages), 0) as shortages,
                COALESCE(SUM(other_deduction), 0) as other_deduction,
                COALESCE(SUM(other_incentives), 0) as other_incentives
            ')
            ->first();

        $this->cash_advance = $totals->cash_advance ?? 0;
        $this->shortages = $totals->shortages ?? 0;
        $this->other_deduction = $totals->other_deduction ?? 0;
        $this->other_incentives = $totals->other_incentives ?? 0;
    }

    public function recalculateTotals(): void
    {
        $basicSalary = (float) ($this->basic_salary ?? 0);
        $overtimeSalary = (float) ($this->overtime_salary ?? 0);
        $nightDiffSalary = (float) ($this->night_diff_salary ?? 0);
        $nightDiffOtSalary = (float) ($this->night_diff_ot_salary ?? 0);
        $sundayOtSalary = (float) ($this->sunday_ot_salary ?? 0);
        $restDayOtSalary = (float) ($this->rest_day_ot_salary ?? 0);

        $totalDeductions = (float) ($this->total_deductions ?? 0);
        $undertimeDeduction = (float) ($this->undertime_deduction ?? 0);

        $cashAdvance = (float) ($this->cash_advance ?? 0);
        $shortages = (float) ($this->shortages ?? 0);
        $otherDeduction = (float) ($this->other_deduction ?? 0);
        $otherIncentives = (float) ($this->other_incentives ?? 0);

        $this->gross_pay =
            $basicSalary
            + $overtimeSalary
            + $nightDiffSalary
            + $nightDiffOtSalary
            + $sundayOtSalary
            + $restDayOtSalary
            + $otherIncentives;

        $this->net_pay =
            $this->gross_pay
            - $totalDeductions
            - $undertimeDeduction
            - $cashAdvance
            - $shortages
            - $otherDeduction;
    }
}