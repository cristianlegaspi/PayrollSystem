<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE cash_advances MODIFY type VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE cash_advances 
            MODIFY type ENUM(
                'cash_advance',
                'motor_assistance',
            ) NOT NULL
        ");
    }
};