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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('rest_day_ot_hours', 8, 2)->default(0)->after('sunday_ot_salary');
            $table->decimal('rest_day_ot_salary', 10, 2)->default(0)->after('rest_day_ot_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['rest_day_ot_hours', 'rest_day_ot_salary']);
        });
    }
};
