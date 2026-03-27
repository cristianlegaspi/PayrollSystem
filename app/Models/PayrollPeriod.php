<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'description',
        'start_date',
        'end_date',
        'status',
        'remarks',
    ];


    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
