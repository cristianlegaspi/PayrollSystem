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
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
             $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->decimal('sss_ee',10,2)->nullable();
            $table->decimal('sss_er',10,2)->nullable();

            $table->decimal('premium_voluntary_ss_contribution',10,2)->nullable();
            $table->decimal('sss_salary_loan',10,2)->nullable();
            $table->decimal('sss_calamity_loan',10,2)->nullable();

            $table->decimal('philhealth_ee',10,2)->nullable();
            $table->decimal('philhealth_er',10,2)->nullable();

            $table->decimal('pagibig_ee',10,2)->nullable();
            $table->decimal('pagibig_er',10,2)->nullable();
            $table->decimal('pagibig_salary_loan',10,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
