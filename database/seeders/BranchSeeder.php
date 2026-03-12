<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('branches')->insert([
            ['id' => 1, 'branch_name' => 'Pulo'],
            ['id' => 2, 'branch_name' => 'Sta Cruz'],
            ['id' => 3, 'branch_name' => 'Dita'],
            ['id' => 4, 'branch_name' => 'San Isidro'],
            ['id' => 5, 'branch_name' => 'Tagapo'],
            ['id' => 6, 'branch_name' => 'All Branch'],
        ]);
    }
}
