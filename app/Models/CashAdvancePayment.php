<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CashAdvancePayment extends Model
{
    protected $fillable = [
        'payment_no',
        'employee_id',
        'cash_advance_id',
        'payment_date',
        'payment_type',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (CashAdvancePayment $payment) {
            if (! empty($payment->payment_no)) {
                return;
            }

            do {
                $paymentNo = 'CAP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            } while (self::where('payment_no', $paymentNo)->exists());

            $payment->payment_no = $paymentNo;
        });

        static::saving(function (CashAdvancePayment $payment) {
            $cashAdvance = CashAdvance::find($payment->cash_advance_id);

            if (! $cashAdvance) {
                return;
            }

            $payment->employee_id = $cashAdvance->employee_id;

            $alreadyPaid = self::query()
                ->where('cash_advance_id', $payment->cash_advance_id)
                ->when($payment->exists, function ($query) use ($payment) {
                    $query->where('id', '!=', $payment->id);
                })
                ->sum('amount');

            $newTotalPayment = (float) $alreadyPaid + (float) $payment->amount;

            if ($newTotalPayment > (float) $cashAdvance->amount) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment/deduction exceeds the remaining cash advance balance.',
                ]);
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }
}