<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Position;
use App\Models\EmploymentStatus;
use App\Models\EmploymentTypes;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure you have at least 1 branch, position, status, and type
        $branch = Branch::first() ?? Branch::create(['branch_name' => 'Main Branch']);
        $position = Position::first() ?? Position::create(['position_name' => 'Assistant Team Leader']);
        $status = EmploymentStatus::first() ?? EmploymentStatus::create(['name' => 'Probationary']);
        $type = EmploymentTypes::first() ?? EmploymentTypes::create(['name' => 'Admin']);

        // Create Employee
        Employee::create([
            'employee_number' => '1',
            'full_name' => 'Alfonso, Wilson C.',
            'position_id' => $position->id,
            'branch_id' => $branch->id,
            'employment_status_id' => $status->id,
            'employment_type_id' => $type->id,
            'daily_rate' => 600,
            'date_hired' => Carbon::create('2026', '03', '06'),
            'date_of_birth' => Carbon::create('2026', '03', '11'),
            'tin' => '423-949-884-000',
            'status' => 'Active',
        ]);

        // Optionally, create more employees using a loop
        for ($i = 2; $i <= 5; $i++) {
            Employee::create([
                'employee_number' => (string)$i,
                'full_name' => "Employee $i",
                'position_id' => $position->id,
                'branch_id' => $branch->id,
                'employment_status_id' => $status->id,
                'employment_type_id' => $type->id,
                'daily_rate' => rand(500, 1000),
                'date_hired' => Carbon::now()->subYears(rand(1,5)),
                'date_of_birth' => Carbon::now()->subYears(rand(20,40)),
                'tin' => '000-000-000-' . $i,
                'status' => 'Active',
            ]);
        }
    }
}