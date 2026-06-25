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
        Schema::create('cash_advance_payments', function (Blueprint $table) {
          $table->id();

            $table->string('payment_no')->unique();

            $table->foreignId('employee_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('cash_advance_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('payment_date');

            $table->enum('payment_type', [
                'payment',
                'deduction',
                'adjustment_less',
            ]);

            $table->decimal('amount', 12, 2)->unsigned();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'payment_date']);
            $table->index('cash_advance_id');
            $table->index('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_advance_payments');
    }
};
