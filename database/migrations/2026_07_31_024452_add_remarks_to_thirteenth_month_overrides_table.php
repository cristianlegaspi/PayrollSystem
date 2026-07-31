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
        Schema::table('thirteenth_month_overrides', function (Blueprint $table) {
              $table->text('remarks')->nullable()->after('gross_pay_override');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thirteenth_month_overrides', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};
