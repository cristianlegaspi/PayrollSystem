<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CashAdvance extends Model
{
    protected $fillable = [
        'ca_no',
        'employee_id',
        'transaction_date',
        'type',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (CashAdvance $cashAdvance) {
            if (! empty($cashAdvance->ca_no)) {
                return;
            }

            do {
                $caNo = 'CA-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            } while (self::where('ca_no', $caNo)->exists());

            $cashAdvance->ca_no = $caNo;
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payments()
    {
        return $this->hasMany(CashAdvancePayment::class);
    }

    public static function increaseTypes(): array
    {
        return [
            'previous_balance',
            'cash_advance',
            'motor_assistance',
            'adjustment_add',
        ];
    }

    public function getPaidAmountAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments->sum('amount');
        }

        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return max((float) $this->amount - $this->paid_amount, 0);
    }

    public function getStatusAttribute(): string
    {
        if ($this->paid_amount <= 0) {
            return 'unpaid';
        }

        if ($this->balance <= 0) {
            return 'paid';
        }

        return 'partial';
    }

    public static function balanceForEmployee(int $employeeId): float
    {
        $totalCashAdvance = self::query()
            ->where('employee_id', $employeeId)
            ->whereIn('type', self::increaseTypes())
            ->sum('amount');

        $totalPayments = CashAdvancePayment::query()
            ->where('employee_id', $employeeId)
            ->sum('amount');

        return (float) $totalCashAdvance - (float) $totalPayments;
    }
}