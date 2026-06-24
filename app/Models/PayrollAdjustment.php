<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollAdjustment extends Model
{
    protected $fillable = [
        'employee_id',
        'payroll_period_id',
        'cash_advance',
        'shortages',
        'other_deduction',
        'other_incentives',
        'remarks',
    ];

    protected $casts = [
        'cash_advance' => 'decimal:2',
        'shortages' => 'decimal:2',
        'other_deduction' => 'decimal:2',
        'other_incentives' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    protected static function booted(): void
    {
        static::saving(function (PayrollAdjustment $adjustment) {
            $adjustment->cash_advance = $adjustment->cash_advance ?? 0;
            $adjustment->shortages = $adjustment->shortages ?? 0;
            $adjustment->other_deduction = $adjustment->other_deduction ?? 0;
            $adjustment->other_incentives = $adjustment->other_incentives ?? 0;
        });

        static::saved(function (PayrollAdjustment $adjustment) {
            $adjustment->syncToPayroll();
        });

        static::deleted(function (PayrollAdjustment $adjustment) {
            $adjustment->syncToPayroll();
        });
    }

    public function syncToPayroll(): void
    {
        $totals = self::query()
            ->where('employee_id', $this->employee_id)
            ->where('payroll_period_id', $this->payroll_period_id)
            ->selectRaw('
                COALESCE(SUM(cash_advance), 0) as cash_advance,
                COALESCE(SUM(shortages), 0) as shortages,
                COALESCE(SUM(other_deduction), 0) as other_deduction,
                COALESCE(SUM(other_incentives), 0) as other_incentives
            ')
            ->first();

        Payroll::where('employee_id', $this->employee_id)
            ->where('payroll_period_id', $this->payroll_period_id)
            ->get()
            ->each(function (Payroll $payroll) use ($totals) {
                $payroll->cash_advance = $totals->cash_advance ?? 0;
                $payroll->shortages = $totals->shortages ?? 0;
                $payroll->other_deduction = $totals->other_deduction ?? 0;
                $payroll->other_incentives = $totals->other_incentives ?? 0;

                $payroll->recalculateTotals();
                $payroll->save();
            });
    }
}