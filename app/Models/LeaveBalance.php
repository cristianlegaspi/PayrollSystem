<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
      protected $fillable = [

        'employee_id',
        'year',
        'annual_credit',
        'used_credit',
        'remaining_credit',

    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
