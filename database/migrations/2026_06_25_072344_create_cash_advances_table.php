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
        Schema::create('cash_advances', function (Blueprint $table) {
            $table->id();

            $table->string('ca_no')->unique();

            $table->foreignId('employee_id')
                ->constrained()
                ->restrictOnDelete();

            $table->date('transaction_date');

            $table->enum('type', [
                'previous_balance',
                'cash_advance',
                'payment',
                'adjustment_add',
                'adjustment_less',
            ]);

            $table->decimal('amount', 12, 2)->unsigned();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'transaction_date']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_advances');
    }
};
