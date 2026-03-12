<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();

            // Attendance
            $table->integer('days_worked')->default(0);
            $table->integer('days_absent')->default(0);
            $table->decimal('undertime_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);

            // Night differential
            $table->decimal('night_diff_hours', 8, 2)->default(0);
            $table->decimal('night_diff_ot_hours', 8, 2)->default(0);

            // Salary computation
            $table->decimal('daily_rate', 10, 2)->default(0);
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('overtime_salary', 12, 2)->default(0);
            $table->decimal('night_diff_salary', 12, 2)->default(0);
            $table->decimal('night_diff_ot_salary', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
