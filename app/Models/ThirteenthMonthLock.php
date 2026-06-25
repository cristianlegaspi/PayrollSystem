<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThirteenthMonthLock extends Model
{
     protected $fillable = [
        'year',
        'is_locked',
    ];

    protected $casts = [
        'year' => 'integer',
        'is_locked' => 'boolean',
    ];
}
