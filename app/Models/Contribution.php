<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    protected $fillable = [

        'employee_id',
        'sss_ee',
        'sss_er',
        'sss_salary_loan',
        'sss_calamity_loan',
        'philhealth_ee',
        'philhealth_er',
        'pagibig_ee',
        'pagibig_er',
        'pagibig_loan',
        'premium_voluntary_ss_contribution',
        'pagibig_salary_loan',


    ];

     public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
