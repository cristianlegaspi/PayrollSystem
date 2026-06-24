<?php

use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Employee::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(PayrollPeriod::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('cash_advance', 12, 2)->default(0);
            $table->decimal('shortages', 12, 2)->default(0);
            $table->decimal('other_deduction', 12, 2)->default(0);
            $table->decimal('other_incentives', 12, 2)->default(0);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'payroll_period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
    }
};