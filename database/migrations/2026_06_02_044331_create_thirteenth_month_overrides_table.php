<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thirteenth_month_overrides', function (Blueprint $table) {
            $table->id();
            
            // Foreign key pointing to your existing employees table
            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();
                
            $table->integer('year');
            $table->unsignedTinyInteger('month'); // 1 to 12
            
            // Decimal allows for exact currency math compared to floats
            $table->decimal('gross_pay_override', 15, 2)->default(0.00);
            
            $table->timestamps();

            // Enforce data integrity: One record per employee, per year, per month
            $table->unique(['employee_id', 'year', 'month'], 'emp_year_month_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thirteenth_month_overrides');
    }
};