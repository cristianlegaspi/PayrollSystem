<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
        protected $fillable = [
        'employee_number',
        'full_name',
        'position_id',
        'branch_id',
        'employment_status_id',
        'employment_type_id',
        'daily_rate',
        'date_hired',
        'date_of_birth',
        'tin',
        'status',
    ];

     protected $casts = [
        'date_hired' => 'date',
        'date_of_birth' => 'date',
    ];

     // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function employmentStatus()
    {
        return $this->belongsTo(EmploymentStatus::class);
    }

    public function employmentType()
    {
        return $this->belongsTo(EmploymentTypes::class);
    }

       public function contribution()
    {
        return $this->hasOne(Contribution::class);
    }

      public function getDisplayNameAttribute()
    {
        return "{$this->employee_number} - {$this->full_name}";
    }
    
    public function dtrs()
    {
        return $this->hasMany(DailyTimeRecord::class);
    }

    public function payrolls()
{
    return $this->hasMany(Payroll::class);
}

}
