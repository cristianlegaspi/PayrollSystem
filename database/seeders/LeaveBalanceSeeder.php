<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveBalance;
use Illuminate\Database\Seeder;

class LeaveBalanceSeeder extends Seeder
{
    public function run(): void
    {
        Employee::all()->each(function ($employee) {

            LeaveBalance::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'year' => now()->year,
                ],
                [
                    'annual_credit' => 5,
                    'used_credit' => 0,
                    'remaining_credit' => 5,
                ]
            );

        });
    }
}