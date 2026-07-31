<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThirteenthMonthOverride extends Model
{
    protected $fillable = [
        'employee_id',
        'year',
        'month',
        'gross_pay_override',
        'remarks',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'gross_pay_override' => 'float',
        
    ];

    /**
     * Relationship back to the employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}