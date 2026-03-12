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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('employee_number')->unique();
            $table->string('full_name');

            $table->foreignId('position_id')
                ->constrained('positions')
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();

            $table->foreignId('employment_status_id')
                ->constrained('employment_statuses')
                ->cascadeOnDelete();

            $table->foreignId('employment_type_id')
                ->constrained('employment_types')
                ->cascadeOnDelete();

            $table->decimal('daily_rate',10,2);
            $table->date('date_hired');
            $table->date('date_of_birth')->nullable();
            $table->string('tin')->nullable();
            $table->enum('status',['Active','Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
