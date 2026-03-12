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
        Schema::create('daily_time_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('work_date');
            $table->enum('status', [
                'on_duty',
                'night_shift',
                'rest_day',
                'legal_holiday',
                'special_holiday',
                'absent_with_pay',
                'absent_without_pay'
            ])->default('on_duty');
            $table->unique(['employee_id', 'work_date']);

            // Shifts
            $table->time('shift1_time_in')->nullable();
            $table->time('shift1_time_out')->nullable();

            $table->time('shift2_time_in')->nullable();
            $table->time('shift2_time_out')->nullable();

            $table->time('shift3_time_in')->nullable();
            $table->time('shift3_time_out')->nullable();

            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('undertime_hours', 5, 2)->default(0);
            $table->decimal('total_hours', 5, 2)->default(0);

            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_time_records');
    }
};
