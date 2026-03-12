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
        Schema::table('users', function (Blueprint $table) {
             $table->foreignId('branch_id')
                ->nullable()
                ->after('id')
                ->constrained('branches')
                ->cascadeOnDelete();

            $table->foreignId('role_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('roles')
                ->cascadeOnDelete();//
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn(['branch_id', 'role_id']);
        });
    }
};
