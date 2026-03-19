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
        Schema::table('employees', function (Blueprint $table) {
             $table->enum('status', ['Active', 'Resigned', 'Terminated'])
              ->default('Active')
              ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
              // Revert to old enum
        $table->enum('status', ['Active', 'Inactive'])
              ->default('Active')
              ->change();
        });
    }
};
